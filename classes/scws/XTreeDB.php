<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\scws;

// Constant Define
define('XDB_FLOAT_CHECK', 3.14);
define('XDB_HASH_BASE', 0xf422f);
define('XDB_HASH_PRIME', 2047);
define('XDB_VERSION', 34);
define('XDB_TAGNAME', 'XDB');
define('XDB_MAXKLEN', 0xf0);

/**
 * scws 分词工具词典管理工具。
 */
class XTreeDB {
	/**
	 * @var resource
	 */
	public $fd         = null;
	public $mode       = 'r';
	public $hash_base  = XDB_HASH_BASE;
	public $hash_prime = XDB_HASH_PRIME;
	public $version    = XDB_VERSION;
	public $fsize      = 0;

	// Private
	private $trave_stack = [];
	private $trave_index = -1;
	private $_sync_nodes = [];
	private $_node_num   = 0;
	private $_cur_depth  = 0;
	// Debug test
	private $_io_times = 0;

	// Constructor Function
	public function __construct($base = 0, $prime = 0) {
		if (0 != $base) $this->hash_base = $base;
		if (0 != $prime) $this->hash_prime = $prime;
	}

	/**
	 * 添加自定义分词.
	 *
	 * @param array  $words
	 * @param string $file
	 * @param string $attr
	 *
	 * @return bool
	 */
	public static function addWords($words, $file, $attr = 'nk') {
		if (empty($words)) {
			return true;
		}
		$xdb = new XTreeDB ();
		if ($xdb->Open($file, 'w')) {
			$rec = [];
			foreach ($words as $word) {
				$k = (ord($word [0]) + ord($word [1])) & 0x3f;
				if (!isset ($rec [ $k ])) {
					$rec [ $k ] = [];
				}
				if (!isset ($rec [ $k ] [ $word ])) {

					$rec [ $k ] [ $word ] = [];
				}
				$rec [ $k ] [ $word ] ['tf']   = '1.0';
				$rec [ $k ] [ $word ] ['idf']  = '1.0';
				$rec [ $k ] [ $word ] ['attr'] = $attr;
				$len                           = mb_strlen($word);
				while ($len > 2) {
					$len  -= 1;
					$temp = mb_substr($word, 0, $len);
					if (!isset ($rec [ $k ] [ $temp ])) {
						$rec [ $k ] [ $temp ] = [];
					}
					$rec [ $k ] [ $temp ] ['part'] = 1;
				}
			}
			if ($rec) {
				$keys = array_keys($rec);
				foreach ($keys as $k) {
					foreach ($rec [ $k ] as $w => $v) {
						$flag = (isset ($v ['tf']) ? 0x01 : 0);
						if (isset ($v ['part'])) {
							$flag |= 0x02;
						}
						$data = pack('ffCa3', $v ['tf'], $v ['idf'], $flag, $v ['attr']);
						$xdb->Put($w, $data);
					}
				}
				$xdb->Optimize();
			}
			$xdb->Close();

			return true;
		} else {
			return false;
		}
	}

	/**
	 * 添加一个单词到词典。
	 *
	 * @param string    $word
	 * @param float|int $tf
	 * @param float|int $idf
	 * @param string    $attr
	 *
	 * @return bool
	 */
	public function addWord($word, $tf, $idf, $attr) {
		$rec = [];
		$k   = (ord($word[0]) + ord($word[1])) & 0x3f;

		$rec [ $k ]                    = [$word => []];
		$rec [ $k ] [ $word ] ['tf']   = floatval($tf);
		$rec [ $k ] [ $word ] ['idf']  = floatval($idf);
		$rec [ $k ] [ $word ] ['attr'] = $attr;

		$len = mb_strlen($word);
		while ($len > 2) {
			$len  -= 1;
			$temp = mb_substr($word, 0, $len);
			if (!isset ($rec [ $k ] [ $temp ])) {
				$rec [ $k ] [ $temp ] = [];
			}
			$rec [ $k ] [ $temp ] ['part'] = 1;
		}

		foreach ($rec [ $k ] as $w => $v) {
			$flag = (isset ($v ['tf']) ? 0x01 : 0);
			if (isset ($v ['part'])) {
				$flag |= 0x02;
			}
			$data = pack('ffCa3', $v ['tf'], $v ['idf'], $flag, $v ['attr']);
			$this->Put($w, $data);
		}

		return true;
	}

	// Open the database: read | write
	public function Open($fpath, $mode = 'r') {
		// open the file
		$this->Close();

		$newdb = false;
		if ($mode == 'w') {
			// write & read only
			if (!($fd = @fopen($fpath, 'rb+'))) {
				if (!($fd = @fopen($fpath, 'wb+'))) {
					trigger_error("XDB::Open(" . basename($fpath) . ",w) failed.", E_USER_WARNING);

					return false;
				}
				// create the header
				$this->_write_header($fd);

				// 32 = header, 8 = Pointer
				$this->fsize = 32 + 8 * $this->hash_prime;
				$newdb       = true;
			}
		} else {
			// read only
			if (!($fd = @fopen($fpath, 'rb'))) {
				trigger_error("XDB::Open(" . basename($fpath) . ",r) failed.", E_USER_WARNING);

				return false;
			}
		}

		// check the header
		if (!$newdb && !$this->_check_header($fd)) {
			trigger_error("XDB::Open(" . basename($fpath) . "), invalid xdb format.", E_USER_WARNING);
			fclose($fd);

			return false;
		}

		// set the variable
		$this->fd   = $fd;
		$this->mode = $mode;
		$this->Reset();

		// lock the file description until close
		if ($mode == 'w') flock($this->fd, LOCK_EX);

		return true;
	}

	// Insert Or Update the value
	public function Put($key, $value) {
		// check the file description
		if (!$this->fd || $this->mode != 'w') {
			trigger_error("XDB::Put(), null db handler or readonly.", E_USER_WARNING);

			return false;
		}

		// check the length
		$klen = strlen($key);
		$vlen = strlen($value);
		if (!$klen || $klen > XDB_MAXKLEN) return false;

		// try to find the old data
		$rec = $this->_get_record($key);
		if (isset($rec['vlen']) && ($vlen <= $rec['vlen'])) {
			// update the old value & length
			if ($vlen > 0) {
				fseek($this->fd, $rec['voff'], SEEK_SET);
				fwrite($this->fd, $value, $vlen);
			}

			if ($vlen < $rec['vlen']) {
				$newlen = $rec['len'] + $vlen - $rec['vlen'];
				$newbuf = pack('I', $newlen);
				fseek($this->fd, $rec['poff'] + 4, SEEK_SET);
				fwrite($this->fd, $newbuf, 4);
			}

			return true;
		}

		// 构造数据
		$new = ['loff' => 0, 'llen' => 0, 'roff' => 0, 'rlen' => 0];
		if (isset($rec['vlen'])) {
			$new['loff'] = $rec['loff'];
			$new['llen'] = $rec['llen'];
			$new['roff'] = $rec['roff'];
			$new['rlen'] = $rec['rlen'];
		}
		$buf = pack('IIIIC', $new['loff'], $new['llen'], $new['roff'], $new['rlen'], $klen);
		$buf .= $key . $value;
		$len = $klen + $vlen + 17;

		$off = $this->fsize;
		fseek($this->fd, $off, SEEK_SET);
		fwrite($this->fd, $buf, $len);
		$this->fsize += $len;

		$pbuf = pack('II', $off, $len);
		fseek($this->fd, $rec['poff'], SEEK_SET);
		fwrite($this->fd, $pbuf, 8);

		return true;
	}

	// Read the value by key
	function Get($key, $debug = false) {
		// check the file description
		if (!$this->fd) {
			trigger_error("XDB::Get(), null db handler.", E_USER_WARNING);

			return false;
		}

		$klen = strlen($key);
		if ($klen == 0 || $klen > XDB_MAXKLEN) return false;

		// get the data?
		$rec = $this->_get_record($key);
		if ($debug) return $rec;

		if (!isset($rec['vlen']) || $rec['vlen'] == 0) return false;

		return $rec['value'];
	}

	// Read the each key & value
	// return array(key => xxx, value => xxx)
	function Next() {
		// check the file description
		if (!$this->fd) {
			trigger_error("XDB::Next(), null db handler.", E_USER_WARNING);

			return false;
		}

		// Traversal the all tree
		if (!($ptr = array_pop($this->trave_stack))) {
			do {
				$this->trave_index++;
				if ($this->trave_index >= $this->hash_prime) break;

				$poff = $this->trave_index * 8 + 32;
				fseek($this->fd, $poff, SEEK_SET);
				$buf = fread($this->fd, 8);
				if (strlen($buf) != 8) {
					$ptr = false;
					break;
				}

				$ptr = unpack('Ioff/Ilen', $buf);
			} while ($ptr['len'] == 0);
		}

		// end the all records?
		if (!$ptr || $ptr['len'] == 0) return false;

		// read the record
		$rec = $this->_tree_get_record($ptr['off'], $ptr['len']);

		// push the left & right
		if ($rec['llen'] != 0) {
			$left = ['off' => $rec['loff'], 'len' => $rec['llen']];
			array_push($this->trave_stack, $left);
		}
		if ($rec['rlen'] != 0) {
			$right = ['off' => $rec['roff'], 'len' => $rec['rlen']];
			array_push($this->trave_stack, $right);
		}

		// return value
		return $rec;
	}

	// Reset the inner pointer
	function Reset() {
		$this->trave_stack = [];
		$this->trave_index = -1;
	}

	// Show the version
	function Version() {
		$ver = (is_null($this) ? XDB_VERSION : $this->version);
		$str = sprintf("%s/%d.%d", XDB_TAGNAME, ($ver >> 5), ($ver & 0x1f));
		if (!is_null($this)) $str .= " <base={$this->hash_base}, prime={$this->hash_prime}>";

		return $str;
	}

	// Close the DB
	function Close() {
		if (!$this->fd) return;

		if ($this->mode == 'w') {
			$buf = pack('I', $this->fsize);
			fseek($this->fd, 12, SEEK_SET);
			fwrite($this->fd, $buf, 4);
			flock($this->fd, LOCK_UN);
		}
		fclose($this->fd);
		$this->fd = false;
	}

	// Optimize the tree
	function Optimize($i = -1) {
		// check the file description
		if (!$this->fd || $this->mode != 'w') {
			trigger_error("XDB::Optimize(), null db handler or readonly.", E_USER_WARNING);

			return false;
		}

		// get the index zone:
		if ($i < 0 || $i >= $this->hash_prime) {
			$i = 0;
			$j = $this->hash_prime;
		} else {
			$j = $i + 1;
		}

		// optimize every index
		while ($i < $j) {
			$this->_optimize_index($i);
			$i++;
		}

		return true;
	}

	// optimize a node
	private function _optimize_index($index) {
		static $cmp = false;
		$poff = $index * 8 + 32;

		// save all nodes into array()
		$this->_sync_nodes = [];
		$this->_load_tree_nodes($poff);

		$count = count($this->_sync_nodes);
		if ($count < 3) return;

		// sync the nodes, sort by key first
		if ($cmp == false) {
			$cmp = function ($a, $b) {
				return strcmp($a['key'], $b['key']);
			};
		}
		//$cmp = create_function('$a,$b', '');
		usort($this->_sync_nodes, $cmp);
		$this->_reset_tree_nodes($poff, 0, $count - 1);
		unset($this->_sync_nodes);
	}

	// load tree nodes
	private function _load_tree_nodes($poff) {
		fseek($this->fd, $poff, SEEK_SET);
		$buf = fread($this->fd, 8);
		if (strlen($buf) != 8) return;

		$tmp = unpack('Ioff/Ilen', $buf);
		if ($tmp['len'] == 0) return;
		fseek($this->fd, $tmp['off'], SEEK_SET);

		$rlen = XDB_MAXKLEN + 17;
		if ($rlen > $tmp['len']) $rlen = $tmp['len'];
		$buf = fread($this->fd, $rlen);

		$rec                 = unpack('Iloff/Illen/Iroff/Irlen/Cklen', substr($buf, 0, 17));
		$rec['off']          = $tmp['off'];
		$rec['len']          = $tmp['len'];
		$rec['key']          = substr($buf, 17, $rec['klen']);
		$this->_sync_nodes[] = $rec;
		unset($buf);

		// left
		if ($rec['llen'] != 0) $this->_load_tree_nodes($tmp['off']);
		// right
		if ($rec['rlen'] != 0) $this->_load_tree_nodes($tmp['off'] + 8);
	}

	// sync the tree
	private function _reset_tree_nodes($poff, $low, $high) {
		if ($low <= $high) {
			$mid  = ($low + $high) >> 1;
			$node = $this->_sync_nodes[ $mid ];
			$buf  = pack('II', $node['off'], $node['len']);

			// left
			$this->_reset_tree_nodes($node['off'], $low, $mid - 1);
			// right
			$this->_reset_tree_nodes($node['off'] + 8, $mid + 1, $high);
		} else {
			$buf = pack('II', 0, 0);
		}

		fseek($this->fd, $poff, SEEK_SET);
		fwrite($this->fd, $buf, 8);
	}

	// Privated Function
	private function _get_index($key) {
		$l = strlen($key);
		$h = $this->hash_base;
		while ($l--) {
			$h += ($h << 5);
			$h ^= ord($key[ $l ]);
			$h &= 0x7fffffff;
		}

		return ($h % $this->hash_prime);
	}

	// draw the tree nodes by off & len
	private function _draw_node($off, $len, $rl = 'T', $icon = '', $depth = 0) {
		if ($rl == 'T') echo '(Ｔ) '; else {
			echo $icon;
			if ($rl == 'L') {
				$icon .= ' ┃';
				echo ' ┟(Ｌ) ';
			} else {
				$icon .= ' 　';
				echo ' └(Ｒ) ';
			}
		}
		if ($len == 0) {
			echo "<NULL>\n";

			return;
		}

		$rec = $this->_tree_get_record($off, $len);
		echo "{$rec['key']} (vlen={$rec['vlen']}, voff={$rec['voff']})\n";
		unset($rec['key'], $rec['value']);

		// debug used
		$this->_node_num++;
		$depth++;
		if ($depth >= $this->_cur_depth) $this->_cur_depth = $depth;

		// Left node & Right Node
		$this->_draw_node($rec['loff'], $rec['llen'], 'L', $icon, $depth);
		$this->_draw_node($rec['roff'], $rec['rlen'], 'R', $icon, $depth);
	}

	// Check XDB Header
	private function _check_header($fd) {
		fseek($fd, 0, SEEK_SET);
		$buf = fread($fd, 32);
		if (strlen($buf) !== 32) return false;
		$hdr = unpack('a3tag/Cver/Ibase/Iprime/Ifsize/fcheck/a12reversed', $buf);
		if ($hdr['tag'] != XDB_TAGNAME) return false;

		// check the fsize
		$fstat = fstat($fd);
		if ($fstat['size'] != $hdr['fsize']) return false;

		// check float?

		$this->hash_base  = $hdr['base'];
		$this->hash_prime = $hdr['prime'];
		$this->version    = $hdr['ver'];
		$this->fsize      = $hdr['fsize'];

		return true;
	}

	// Write XDB Header
	private function _write_header($fd) {
		$buf = pack('a3CiiIfa12', XDB_TAGNAME, $this->version, $this->hash_base, $this->hash_prime, 0, XDB_FLOAT_CHECK, '');

		fseek($fd, 0, SEEK_SET);
		fwrite($fd, $buf, 32);
	}

	// get the record by first key
	private function _get_record($key) {
		$this->_io_times = 1;
		$index           = ($this->hash_prime > 1 ? $this->_get_index($key) : 0);
		$poff            = $index * 8 + 32;
		fseek($this->fd, $poff, SEEK_SET);
		$buf = fread($this->fd, 8);

		if (strlen($buf) == 8) $tmp = unpack('Ioff/Ilen', $buf); else $tmp = ['off' => 0, 'len' => 0];

		return $this->_tree_get_record($tmp['off'], $tmp['len'], $poff, $key);
	}

	// get the record by tree
	private function _tree_get_record($off, $len, $poff = 0, $key = '') {
		if ($len == 0) return (['poff' => $poff]);
		$this->_io_times++;

		// get the data & compare the key data
		fseek($this->fd, $off, SEEK_SET);
		$rlen = XDB_MAXKLEN + 17;
		if ($rlen > $len) $rlen = $len;
		$buf  = fread($this->fd, $rlen);
		$rec  = unpack('Iloff/Illen/Iroff/Irlen/Cklen', substr($buf, 0, 17));
		$fkey = substr($buf, 17, $rec['klen']);
		$cmp  = ($key ? strcmp($key, $fkey) : 0);
		if ($cmp > 0) {
			// --> right
			unset($buf);

			return $this->_tree_get_record($rec['roff'], $rec['rlen'], $off + 8, $key);
		} else if ($cmp < 0) {
			// <-- left
			unset($buf);

			return $this->_tree_get_record($rec['loff'], $rec['llen'], $off, $key);
		} else {
			// found!!
			$rec['poff'] = $poff;
			$rec['off']  = $off;
			$rec['len']  = $len;
			$rec['voff'] = $off + 17 + $rec['klen'];
			$rec['vlen'] = $len - 17 - $rec['klen'];
			$rec['key']  = $fkey;

			fseek($this->fd, $rec['voff'], SEEK_SET);
			$rec['value'] = fread($this->fd, $rec['vlen']);

			return $rec;
		}
	}
}
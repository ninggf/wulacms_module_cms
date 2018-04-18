<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\form;

use wulaphp\app\App;
use wulaphp\form\FormTable;
use wulaphp\util\ArrayCompare;

class DefaultPageForm extends FormTable {
	private $_model_refid;
	public  $table = null;

	public function __construct($id, $db = null) {
		$this->_model_refid = $id;
		parent::__construct(true, $db);
	}

	/**
	 * @param $sfields
	 *
	 * @throws \Exception
	 */
	protected function initialize(&$sfields) {
		$db     = App::db();
		$fields = $db->select('CMF.*')->from('{cms_model_field} AS CMF')->join('{cms_model} AS CM', 'CMF.model=CM.id')->where(['CM.refid' => $this->_model_refid])->toArray();
		if ($fields) {
			$row = 1000000;
			foreach ($fields as $k => $v) {
				$ll = @json_decode($v['layout'], true);
				if ($ll) {
					$v['lcol']   = $ll['row'] . ',' . (is_numeric($ll['col']) ? 'col-xs-' . $ll['col'] : $ll['col']);
					$v['layout'] = intval($ll['row']) * 10000 + intval($ll['sort']);
				} else {
					$v['lcol']   = (++$row) . ',col-xs-12';
					$v['layout'] = $row;
				}
				$fields[ $k ] = $v;
			}
			usort($fields, ArrayCompare::compare('layout'));
			foreach ($fields as $v) {
				switch ($v['type']) {
					case 'bool':
					case 'int':
					case 'float':
					case 'date':
					case 'array':
						$f['type'] = $v['type'];
						break;
					case 'varchar':
					case 'text':
					default:
						$f['type'] = 'string';
				}
				if ($v['dataSource']) {
					$f['dataSource'] = $v['dataSource'];
					$f['dsCfg']      = $v['dsCfg'];
				}
				$f['label']  = $v['label'];
				$f['var']    = $v['field'];
				$f['name']   = $v['name'];
				$f['layout'] = $v['lcol'];
				$f['note']   = $v['note'];
				if ($v['fieldCfg']) {
					$f['option'] = @json_decode($v['fieldCfg'], true);
				}
				$sfields [] = $this->addField($v['name'], $f, $v['default']);
				if ($v['required']) {
					$this->addRule($v['name'], ['required']);
				}
			}
		} else {
			throw_exception('未定义字段');
		}
	}
}
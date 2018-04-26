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

class Scwser {
	/**
	 * 得到关键词列表.
	 *
	 * @param string      $string
	 * @param int         $count 分词数量
	 * @param string|null $dict  自定义字典文件
	 * @param bool        $add   字典文件使用方式
	 *
	 * @return array
	 */
	public static function scws($string = '', $count = 5, $dict = null, $add = false) {
		static $scwss = [], $dicts = [];
		$keywords = [];
		if (extension_loaded('scws') && $string) {
			$pid = defined('ARTISAN_TASK_PID') ? posix_getpid() : 0;
			if (!isset($scwss[ $pid ])) {
				$scws = scws_new();
				$scws->set_charset('utf8');
				$scwss[ $pid ] = $scws;
			} else {
				$scws = $scwss[ $pid ];
			}
			$attr = null;
			if ($dict && is_file($dict)) {
				if (!isset($dicts[ $dict ])) {
					if ($add) {
						if (preg_match('/.+\.txt$/i', $dict)) {
							$scws->add_dict($dict, SCWS_XDICT_TXT);
						} else {
							$scws->add_dict($dict);
						}
						$dicts[ $dict ] = 1;
						$od             = trailingslashit(ini_get('scws.default.fpath')) . 'dict.utf8.xdb';
						if (is_file($od)) {
							$scws->add_dict($od);
						}
						$scws->set_multi(15);
					} else {
						$scws1 = scws_new();
						$scws1->set_charset('utf8');
						@$scws1->set_dict($dict);
						$attr = 'nk';
						$scws1->set_multi(SCWS_MULTI_NONE);

						$keywords = self::doit($scws1, $string, $count, $attr);
						$scws1->close();

						return $keywords;
					}
				}
			} else {
				$scws->set_multi(15);
			}
			$keywords = self::doit($scws, $string, $count, $attr);
		}

		return $keywords;
	}

	private static function doit($scws, $string, $count, $attr) {
		$keywords = [];
		$scws->set_duality(false);
		$scws->set_ignore(true);
		$scws->send_text($string);
		$tmp = $scws->get_tops($count, $attr);
		if ($tmp) {
			foreach ($tmp as $keyword) {
				$keywords [] = $keyword ['word'];
			}
		}

		return $keywords;
	}

	/**
	 * 将汉字进行转换.
	 *
	 * @param string $keywords
	 *
	 * @return string
	 */
	public static function convert($keywords) {
		$keywords = json_encode($keywords);

		return str_replace(['\u', '"', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'], [
			'uu',
			'',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J'
		], $keywords);
	}
}
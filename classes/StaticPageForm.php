<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes;

use wulaphp\app\App;
use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class StaticPageForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $id = 0;
	/**
	 * URL
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @callback (checkrt(id)) => URL不可用
	 * @layout 2,col-xs-6
	 */
	public $url;
	/**
	 * 页面名称
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 2,col-xs-2
	 */
	public $title2;
	/**
	 * 页面模板
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @pattern (/\.tpl$/) => 必须是.tpl结尾.
	 * @layout 2,col-xs-2
	 */
	public $template_file;
	/**
	 * 缓存时间
	 * @var \backend\form\TextField
	 * @type int
	 * @pattern (/^(-1|0|[1-9]\d*)$/) => 只能是大于-1的整数.
	 * @layout 2,col-xs-2
	 * @note   -1不缓存0系统配置,单位秒.
	 */
	public $expire = 0;

	/**
	 * 默认页面标题
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-6
	 */
	public $title;
	/**
	 * 默认页面关键词
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-6
	 */
	public $keywords;
	/**
	 * 默认页面描述
	 * @var \backend\form\TextareaField
	 * @type string
	 * @layout 8,col-xs-12
	 * @option {"row":3}
	 */
	public $description;
	/**
	 * 禁止搜索引擎索引
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 9,col-xs-12
	 */
	public $noindex = 0;

	/**
	 * @param $value
	 * @param $data
	 * @param $msg
	 *
	 * @return bool
	 * @throws
	 */
	public function checkrt($value, $data, $msg) {
		$md5            = md5($value);
		$db             = App::db();
		$where['route'] = $md5;
		if (isset($data['id']) && $data['id']) {
			$where['id <>'] = $data['id'];
		}
		if ($db->select()->from('{cms_router}')->where($where)->exist('id')) {
			return $msg;
		}

		return true;
	}
}
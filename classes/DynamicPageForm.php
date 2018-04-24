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

use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class DynamicPageForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $id = 0;
	/**
	 * 页面URL路由规则
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @callback (checkrt(id)) => URL规则不正确
	 * @layout 2,col-xs-6
	 * @note   可用:(d)表示数字;(s)表示字母与'-';(*)表示数字，字母与'-'.
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
	 */
	public function checkrt($value, $data, $msg) {

		return DynamicPage::parseRoute($value) ? true : $msg;
	}
}
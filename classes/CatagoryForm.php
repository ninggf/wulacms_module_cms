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

class CatagoryForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $id = 0;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $channel = 0;
	/**
	 * 栏目名称
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 2,col-xs-3
	 */
	public $title2;
	/**
	 * 存储目录
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @pattern (/^[0-9a-z][0-9a-z]*$/) => 只能是小字母、数字的组合
	 * @callback (checkp(channel,id)) => 存储目录不可用
	 * @layout 2,col-xs-3
	 * @note   请谨慎填写，后期不可修改
	 */
	public $store_path;
	/**
	 * 缓存时间
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 2,col-xs-3
	 * @note   0表示系统默认缓存时间
	 */
	public $expire = 0;
	/**
	 * 显示排序
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @max (9999) => 最大值为9999
	 * @layout 2,col-xs-3
	 * @note   值越小越靠前
	 */
	public $display_sort = 9999;

	/**
	 * 文件名
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @pattern (/^[a-z0-9]+\.html$/) => 字母和数字组成的以.html结尾.
	 * @layout 3,col-xs-6
	 */
	public $url = 'index.html';

	/**
	 * 封面页模板
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @pattern (/\.tpl$/) => 必须是.tpl结尾.
	 * @layout 3,col-xs-3
	 */
	public $template_file = 'catagory.tpl';
	/**
	 * 列表页模板
	 * @var \backend\form\TextField
	 * @type string
	 * @pattern (/\.tpl$/) => 必须是.tpl结尾.
	 * @layout 3,col-xs-3
	 */
	public $template_file2;
	/**
	 * 页面标题
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-6
	 */
	public $title;
	/**
	 * 页面关键词
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-6
	 */
	public $keywords;
	/**
	 * 页面描述
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
	 * 检测存储目录是否可用.
	 *
	 * @param string $value
	 * @param array  $data
	 * @param string $msg
	 *
	 * @return bool
	 */
	public function checkp($value, $data, $msg) {
		try {
			$db                  = App::db();
			$channel             = $data['channel'];
			$where['store_path'] = $value;
			$where['channel']    = $channel;
			if (isset($data['id']) && $data['id']) {
				$where['id <>'] = $data['id'];
			}
			$rst = $db->select('*')->from('{cms_channel} AS CH')->join('{cms_page_field} AS CP', 'CH.page_id = CP.page_id')->where($where)->exist('CP.page_id');
			if ($rst) {
				return $msg;
			}

			return true;
		} catch (\Exception $e) {
			return $msg;
		}
	}

	public function alterFieldOptions($name, &$options) {
		if ($this->_tableData['id'] && in_array($name, ['store_path'])) {
			$options['readonly'] = true;
			$options['note']     = null;
		}
	}
}
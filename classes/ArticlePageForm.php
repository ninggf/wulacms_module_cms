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

use cms\classes\model\CmsModelTable;
use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class ArticlePageForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $id = 0;
	/**
	 * 标题
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 2,col-xs-8
	 */
	public $title = '';
	/**
	 * 副标题
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 2,col-xs-4
	 */
	public $title2 = '';

	/**
	 * 页面属性
	 * @var \backend\form\MultipleCheckboxFiled
	 * @type list
	 * @dsCfg ::modelFlags
	 * @layout 3,col-xs-8
	 * @option {"inline":1}
	 */
	public $flags = '';
	/**
	 * 页面标签(多个标签用','分隔)
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 3,col-xs-4
	 */
	public $tags = '';

	/**
	 * 页面URL
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 4,col-xs-4
	 * @note   不填写自动将自动生成.
	 */
	public $url = '';
	/**
	 * 页面模板
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 4,col-xs-4
	 * @note   不填写将使用栏目设置
	 */
	public $template_file = '';
	/**
	 * 页面缓存
	 * @var \backend\form\TextField
	 * @type int
	 * @pattern (/^(-1|0|[1-9]\d*)$/) => 只能是大于-1的整数.
	 * @layout 4,col-xs-4
	 * @note   -1不缓存0默认,单位秒.
	 */
	public $expire = 0;

	/**
	 * 页面插图
	 * @var \backend\form\FileUploaderField
	 * @type string
	 * @layout 5,col-xs-4
	 * @option {"exts":"jpg,gif,png,jpeg","width":120,"height":90,"noWater":1,"maxFileSize":"1mb"}
	 */
	public $image;
	/**
	 * 页面插图二
	 * @var \backend\form\FileUploaderField
	 * @type string
	 * @layout 5,col-xs-4
	 * @option {"exts":"jpg,gif,png,jpeg","width":120,"height":90,"noWater":1,"maxFileSize":"1mb"}
	 */
	public $image1;
	/**
	 * 页面插图三
	 * @var \backend\form\FileUploaderField
	 * @type string
	 * @layout 5,col-xs-4
	 * @option {"exts":"jpg,gif,png,jpeg","width":120,"height":90,"noWater":1,"maxFileSize":"1mb"}
	 */
	public $image2;
	/**
	 * 作者
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-3
	 */
	public $author;
	/**
	 * 来源
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-3
	 */
	public $source;
	/**
	 * 发布时间
	 * @var \backend\form\DatepickerField
	 * @type string
	 * @layout 6,col-xs-3
	 */
	public $publish_day;
	/**
	 * &nbsp;
	 * @var \backend\form\TimepickerField
	 * @type string
	 * @layout 6,col-xs-3
	 */
	public $publish_hm;
	/**
	 * 关键词
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 7,col-xs-6
	 */
	public $keywords = '';
	/**
	 * 相关页面
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 7,col-xs-6
	 * @note   多个页面使用','分隔
	 */
	public $related_pages;
	/**
	 * 描述
	 * @var \backend\form\TextareaField
	 * @type string
	 * @layout 8,col-xs-12
	 */
	public $description = '';
	/**
	 * 内容
	 * @var \backend\form\WysiwygField
	 * @type string
	 * @layout 9,col-xs-12
	 * @option {"height":350,"btns":"font,size,color,style,link,img,pager"}
	 */
	public $content;

	public function alterFieldOptions($name, &$options) {
		if ($this->_tableData && $this->_tableData['id'] && $name == 'url') {
			$options['readonly'] = true;
		}
	}

	public function modelFlags() {
		$model = new CmsModelTable();
		$flags = $model->getFlags('article');

		return $flags;
	}
}
<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\block\form;

use cms\block\model\CmsBlockItem;
use wulaphp\validator\JQueryValidator;

class CmsBlockItemForm extends CmsBlockItem {
	use JQueryValidator;
	public $table = 'cms_block_item';

	public function __construct($db = null) {
		parent::__construct(true, $db === true ? null : $db);
	}

	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 * @layout 1,col-xs-3
	 */
	public $id;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 * @layout 1,col-xs-3
	 * @required
	 */
	public $block_id;

	/**
	 * @var \backend\form\HiddenField
	 * @type string
	 * @layout 1,col-xs-3
	 * @required
	 */
	public $pn;
	/**
	 * 标题
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 5,col-xs-8
	 */
	public $title;
	/**
	 * 副标题
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 5,col-xs-4
	 */
	public $title2;
	/**
	 * URL
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 7,col-xs-8
	 */
	public $url;
	/**
	 * 显示排序
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 7,col-xs-2
	 */
	public $sort = 999;
	/**
	 * 绑定页面
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 7,col-xs-2
	 */
	public $page_id = 0;
	/**
	 * 图1
	 * @var \backend\form\FileUploaderField
	 * @type string
	 * @layout 10,col-xs-4
	 * @option {"exts":"jpg,jpeg,gif,png","noWater":1,"auto":1,"maxFileSize":"1mb"}
	 */
	public $image;
	/**
	 * 图2
	 * @var \backend\form\FileUploaderField
	 * @type string
	 * @layout 10,col-xs-4
	 * @option {"exts":"jpg,jpeg,gif,png","noWater":1,"auto":1,"maxFileSize":"1mb"}
	 */
	public $image1;
	/**
	 * 图3
	 * @var \backend\form\FileUploaderField
	 * @type string
	 * @layout 10,col-xs-4
	 * @option {"exts":"jpg,jpeg,gif,png","noWater":1,"auto":1,"maxFileSize":"1mb"}
	 */
	public $image2;
	/**
	 * 自定义数值1
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 20,col-xs-4
	 */
	public $num = 0;
	/**
	 * 自定义数值2
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 20,col-xs-4
	 */
	public $num1 = 0;
	/**
	 * 自定义数值3
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 20,col-xs-4
	 */
	public $num2 = 0;
}
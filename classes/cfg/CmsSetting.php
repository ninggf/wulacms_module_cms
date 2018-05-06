<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\cfg;

use backend\classes\Setting;
use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class CmsSetting extends Setting {
	public function getForm($group = '') {
		return new CmsSettingForm(true);
	}

	public function getName() {
		return 'CMS设置';
	}
}

class CmsSettingForm extends FormTable {
	use JQueryValidator;
	public $table = null;
	/**
	 * 启用审核机制
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 10,col-xs-12
	 */
	public $approveEnabled;
	/**
	 * 启用版本控制
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 20,col-xs-2
	 */
	public $vcEnabled;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   最多保留版本数
	 * @layout 20,col-xs-4
	 */
	public $maxVer = 5;

	/**
	 * 下载远程图片
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 40,col-xs-2
	 */
	public $downPic;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   下载超时(单位秒)
	 * @layout 40,col-xs-4
	 */
	public $down_timeout = 30;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type string
	 * @pattern (/^[\d]+x[\d]+$/) => 格式为:宽x高
	 * @note   重设尺寸(格式:宽x高)
	 * @layout 40,col-xs-4
	 */
	public $resize_img;
	/**
	 * 关键词提取
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 50,col-xs-2
	 */
	public $scwsEnabled;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   最多提取关键词数
	 * @layout 50,col-xs-4
	 */
	public $scwsCnt = 5;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type string
	 * @note   分词词典文件(放在conf目录中)
	 * @layout 50,col-xs-6
	 */
	public $scwsDict = '';
	/**
	 * 描述提取
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 60,col-xs-2
	 */
	public $descEnabled;

	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   提取字符数量
	 * @layout 60,col-xs-4
	 */
	public $descCnt = 255;
	/**
	 * 启用内链
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 70,col-xs-2
	 */
	public $tagEnabled;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   每页最多内链数
	 * @layout 70,col-xs-4
	 */
	public $tags_count = 10;
	/**
	 *
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @note   每个标签最多替换次数(0表示全部替换)
	 * @layout 70,col-xs-6
	 */
	public $tag_count = 0;
	/**
	 * 文件存储器
	 * @var \backend\form\TextField
	 * @type string
	 * @pattern (/^[a-z]+(:([a-z][a-z\d]*=[^=;]+)(;([a-z][a-z\d]*=[^=;]+))*)*$/) => 文件存储器配置不正确
	 * @note   不同存储器配置请参考相应的文档
	 * @layout 500,col-xs-12
	 */
	public $storage = 'file:path=storage';
}
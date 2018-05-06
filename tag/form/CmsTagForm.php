<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\tag\form;

use cms\tag\model\CmsTag;
use wulaphp\validator\JQueryValidator;

class CmsTagForm extends CmsTag {
	use JQueryValidator;
	public $table = 'cms_tag';

	public function __construct($db = null) {
		parent::__construct(true, $db === true ? null : $db);
	}

	/**
	 *
	 * @var \backend\form\HiddenField
	 * @type int
	 * @layout 1,col-xs-12
	 */
	public $id;
	/**
	 * 标签
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 10,col-xs-4
	 */
	public $tag;
	/**
	 * 标题
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 10,col-xs-8
	 */
	public $title;
	/**
	 * 权重（内链优先级）
	 * @var \backend\form\TextField
	 * @type int
	 * @required
	 * @digits
	 * @layout 20,col-xs-4
	 */
	public $sort = 9999;
	/**
	 * 链接(URL)
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 20,col-xs-8
	 */
	public $url;
}
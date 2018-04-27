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

use cms\block\model\CmsBlock;
use wulaphp\validator\JQueryValidator;

class CmsBlockForm extends CmsBlock {
	use JQueryValidator;
	public $table = 'cms_block';

	public function __construct($db = null) {
		parent::__construct(true, $db === true ? null : $db);
	}

	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 * @layout 1,col-xs-6
	 */
	public $id;
	/**
	 * 区块所在页面
	 * @var \backend\form\ComboxField
	 * @type string
	 * @required
	 * @option {"url":"cms/block/ptip","allowClear":1}
	 * @layout 2,col-xs-12
	 */
	public $page;
	/**
	 * 区块名称
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @callback (checkName(id,page)) => 区块已经存在或区块所在页面为空
	 * @layout 3,col-xs-12
	 */
	public $name;

	public function checkName($value, $data, $message) {
		if (!$data['page']) {
			return $message;
		}
		$where['page'] = $data['page'];
		$where['name'] = $value;
		if ($data['id']) {
			$where['id <>'] = $data['id'];
		}

		if ($this->exist($where)) {
			return $message;
		}

		return true;
	}
}
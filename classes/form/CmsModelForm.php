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

use cms\classes\ModelDoc;
use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class CmsModelForm extends FormTable {
	use JQueryValidator;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $id = 0;
	/**
	 * 模型名称
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 2,col-xs-6
	 */
	public $name;
	/**
	 * 模型标识
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @callback (check(id)) => 模型标识已经存在
	 * @layout 2,col-xs-6
	 */
	public $refid;
	/**
	 * 模型属性
	 * @var \backend\form\TextField
	 * @type string
	 * @note   用逗号分隔,每个属性不超过6个汉字.
	 * @layout 3,col-xs-12
	 */
	public $flags;

	public function alterFieldOptions($name, &$options) {
		if ($this->_tableData && $name == 'refid') {
			$doc = ModelDoc::getDoc($this->_tableData['refid']);
			if ($doc->isNative()) {
				$options['readonly'] = true;
			}
		}
	}

	public function check($value, $data, $msg) {
		$where['refid'] = $value;
		if ($data['id']) {
			$where['id <>'] = $data['id'];
		}
		if (!$this->exist($where)) {
			return true;
		}

		return $msg;
	}
}
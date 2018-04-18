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

class CmsModelFieldForm extends FormTable {
	use JQueryValidator;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $id;
	/**
	 * @var \backend\form\HiddenField
	 * @type int
	 */
	public $model;
	/**
	 * 字段名称
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 2,col-xs-4
	 */
	public $label;
	/**
	 * 字段
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @callback (check(model,id))=>字段已经存在
	 * @layout 2,col-xs-4
	 */
	public $name;
	/**
	 * 提示文本
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 2,col-xs-4
	 */
	public $note;
	/**
	 * 表单控件
	 * @var \backend\form\SelectField
	 * @type string
	 * @required
	 * @layout 3,col-xs-4
	 * @dsCfg ::getControlls
	 */
	public $field;
	/**
	 * 字段类型
	 * @var \backend\form\SelectField
	 * @type string
	 * @required
	 * @see    param
	 * @data   varchar=字符串&text=文本&int=整数&float=浮点数&bool=布尔型&date=日期&array=数组
	 * @layout 3,col-xs-4
	 */
	public $type = 'text';
	/**
	 * 字段长度
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 3,col-xs-4
	 */
	public $length = 0;
	/**
	 * 数据源
	 * @var \backend\form\SelectField
	 * @type string
	 * @dsCfg ::getDatasource
	 * @layout 4,col-xs-4
	 */
	public $dataSource = '';
	/**
	 * 简单数据源配置
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 4,col-xs-8
	 * @note   JSON:JSON数据;参数:URL请求参数。其它数据源请保留原样。
	 */
	public $dsCfg;
	/**
	 * 布局-所在组
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 5,col-xs-4
	 */
	public $layout_row;
	/**
	 * 布局-长度
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 5,col-xs-4
	 */
	public $layout_col;
	/**
	 * 布局-排序
	 * @var \backend\form\TextField
	 * @type int
	 * @digits
	 * @layout 5,col-xs-4
	 */
	public $layout_sort;

	/**
	 * 索引
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 8,col-xs-2
	 */
	public $index;
	/**
	 * 唯一
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 8,col-xs-2
	 */
	public $unique;
	/**
	 * 必填字段
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 8,col-xs-4
	 */
	public $required;
	/**
	 * 默认值
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 8,col-xs-4
	 */
	public $default;

	public function getControlls() {
		$controlls = [];
		$cntls     = ModelDoc::getFormControlls();
		/**@var \wulaphp\form\FormField $cntl */
		foreach ((array)$cntls as $id => $cntl) {
			$controlls[ $id ] = $cntl->getName();
		}

		return $controlls;
	}

	public function getDatasource() {
		$dss     = ModelDoc::getDataSources();
		$dss[''] = '无';

		return $dss;
	}

	public function check($value, $data, $msg) {
		if (in_array($value, ['table', 'autoIncrement', 'tableClz', 'tableName', 'errors', 'alias', 'primaryKey'])) {
			return $msg;
		}

		$where['name'] = $value;
		if (!$data['model']) {
			return $msg;
		}
		$where['model'] = $data['model'];
		if ($data['id']) {
			$where['id <>'] = $data['id'];
		}
		if (!$this->exist($where)) {
			return true;
		}

		return $msg;
	}

	public function newField($data) {
		return $this->insert($data);
	}

	public function updateField($id, $data) {
		return $this->update($data, $id);
	}

	public function removeField($id) {
		return $this->delete($id);
	}
}
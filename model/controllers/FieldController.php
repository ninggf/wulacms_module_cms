<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\model\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\classes\DefaultPage;
use cms\classes\form\CmsModelFieldForm;
use cms\classes\model\CmsModelTable;
use wulaphp\form\FormField;
use wulaphp\form\FormTable;
use wulaphp\form\providor\FieldDataProvidor;
use wulaphp\form\providor\JsonDataProvidor;
use wulaphp\form\providor\LineDataProvidor;
use wulaphp\form\providor\ParamDataProvidor;
use wulaphp\form\providor\TableDataProvidor;
use wulaphp\io\Ajax;
use wulaphp\io\Response;
use wulaphp\util\ArrayCompare;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * Class FieldController
 * @package cms\model\controllers
 * @acl     m:site/model
 * @accept  cms\classes\form\CmsModelFieldForm
 */
class FieldController extends IFramePageController {
	use JQueryValidatorController;

	public function index($id) {
		$id = intval($id);
		if (!$id) {
			Response::respond(404, '模型不存在');
		}
		$table = new CmsModelTable();
		$field = $table->get($id)->get();
		if (!$field) {
			Response::respond(404, '模型不存在');
		}
		$data['model'] = $id;

		return $this->render($data);
	}

	public function edit($id = 0, $model = 0) {
		$mf['model'] = $model;
		$mf['id']    = $id;

		$form = new CmsModelFieldForm(true);
		if (!$id) {
			$form->inflateByData($mf);
		} else {
			$fd     = $form->get($id)->ary();
			$layout = $fd['layout'];
			$layout = @json_decode($layout, true);
			if ($layout) {
				$fd['layout_row']  = $layout['row'];
				$fd['layout_col']  = $layout['col'];
				$fd['layout_sort'] = $layout['sort'];
			}
			$form->inflateByData($fd);
		}
		$data['form']  = BootstrapFormRender::v($form);
		$data['rules'] = $form->encodeValidatorRule($this);

		return view($data);
	}

	public function savePost($id) {
		$form = new CmsModelFieldForm(true);
		$data = $form->inflate();
		try {
			if (empty($data['model'])) {
				return Ajax::error('无法保存字段,不知道它是哪个模型的字段!');
			}
			$form->validate();
			$layout['row']  = $data['layout_row'];
			$layout['col']  = $data['layout_col'];
			$layout['sort'] = $data['layout_sort'];
			unset($data['layout_row'], $data['layout_col'], $data['layout_sort'], $data['id']);
			$data['layout'] = json_encode($layout);
			if ($data['type'] == 'varchar' && !$data['length']) {
				$data['length'] = 128;
			}
			$table = new CmsModelFieldForm();
			if ($id) {
				$rst = $table->updateField($id, $data);

			} else {
				$rst = $table->newField($data);
			}
			if ($rst) {
				return Ajax::reload('#field-list', $id ? '字段修改成功' : '字段已经添加');
			} else {
				return Ajax::error('无法保存字段,请联系管理员');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('EditForm', $ve->getErrors());
		}
	}

	public function fields($model) {
		$data         = [];
		$model        = intval($model);
		$table        = new CmsModelFieldForm();
		$data['rows'] = $table->select('id,label,name,layout')->asc('name')->where(['model' => $model])->toArray();
		foreach ($data['rows'] as $k => $v) {
			$ll = @json_decode($v['layout'], true);
			if ($ll) {
				$v['row']    = $ll['row'] . '/' . $ll['col'];
				$v['layout'] = intval($ll['row']) * 10000 + intval($ll['sort']);
			} else {
				$v['row']    = '';
				$v['layout'] = 1000000;
			}
			$data['rows'][ $k ] = $v;
		}
		usort($data['rows'], ArrayCompare::compare('layout'));

		return view($data);
	}

	public function preview($model) {
		$table = new CmsModelTable();
		$field = $table->get($model)->get();
		if (!$field) {
			Response::respond(404, '模型不存在');
		}
		$mdoc = new DefaultPage($field['refid'], $field['name']);
		$form = $mdoc->getDynamicForm();
		if ($form instanceof FormTable) {
			$data['form'] = BootstrapFormRender::v($form);
		} else {
			$data['msg'] = '暂无预览';
		}

		return view($data);
	}

	public function cfg($id) {
		$form  = new CmsModelFieldForm();
		$field = $form->get($id)->get();
		if (!$field) {
			Response::error('字段不存在');
		}
		$data = [];
		//字段属性表单
		$fieldCls = $field['field'];
		if (is_subclass_of($fieldCls, FormField::class)) {
			/**@var FormField $fieldClz */
			$fieldClz = new $fieldCls('', null);
			$fform    = $fieldClz->getOptionForm();
			if ($fform) {
				$fieldCfg = @json_decode($field['fieldCfg'], true);
				$fform->inflateByData($fieldCfg);
				$data['fform'] = BootstrapFormRender::v($fform);
			}
			$dataSource = $field['dataSource'];
			$dsCfg      = $field['dsCfg'];
			//数据源表单
			$form = $this->getCfgForm($dataSource, $dsCfg);
			if ($form) {
				$data['dsForm'] = BootstrapFormRender::v($form);
				if (method_exists($form, 'encodeValidatorRule')) {
					$form->applyRules($fform);
					$data['rules'] = $form->encodeValidatorRule($this);
				}
			}
		} else {
			Response::error('字段不存在');
		}
		$data['id'] = $id;

		return view($data);
	}

	/**
	 * 保存配置.
	 *
	 * @param string|int $id
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function cfgPost($id) {
		$table = new CmsModelFieldForm();
		$field = $table->get($id)->get();
		if (!$field) {
			Response::error('字段不存在');
		}
		$data = [];
		//字段属性表单
		$fieldCls = $field['field'];
		if (is_subclass_of($fieldCls, FormField::class)) {
			/**@var FormField $fieldClz */
			$fieldClz = new $fieldCls('', null);
			$fform    = $fieldClz->getOptionForm();
			if ($fform) {
				$cfg1 = $fform->inflate();
				foreach ($cfg1 as $k => $v) {
					if (!$v) {
						$cfg1[ $k ] = null;
					}
				}
				$data['fieldCfg'] = json_encode($cfg1);
			} else {
				$data['fieldCfg'] = '';
			}
			$dataSource = $field['dataSource'];
			$dsCfg      = $field['dsCfg'];
			//数据源表单
			$form = $this->getCfgForm($dataSource, $dsCfg);
			if ($form) {
				$cfg2 = $form->inflate('', false, true);
				if (method_exists($form, 'validate')) {
					try {
						$form->validate();
						if (isset($cfg2['dsCfg'])) {
							$data['dsCfg'] = $cfg2['dsCfg'];
						} else {
							foreach ($cfg2 as $k => $v) {
								if (!$v) {
									$cfg2[ $k ] = null;
								}
							}
							$data['dsCfg'] = json_encode($cfg2);
						}
					} catch (ValidateException $ve) {
						return Ajax::validate('CfgForm', $ve->getErrors());
					}
				}
			} else {
				$data['dsCfg'] = '';
			}
			if ($data) {
				$table->updateField($id, $data);
			}

			return Ajax::reload('#form-preview', '配置完成');
		} else {
			return Ajax::error('字段控件类不存在');
		}
	}

	public function del($id) {
		if (empty($id)) {
			return Ajax::error('请告诉我你要删除哪个字段啊,小姐姐');
		}
		$table = new CmsModelFieldForm();
		$table->removeField($id);

		return Ajax::reload('#field-list', '字段删除成功');
	}

	/**
	 * 字段配置表单.
	 *
	 * @param string $dataSource
	 * @param string $dsCfg
	 *
	 * @return \wulaphp\form\FormTable
	 */
	private function getCfgForm($dataSource, $dsCfg) {
		if (!$dataSource) {
			return null;
		}
		$pd = new FieldDataProvidor(null, null, $dsCfg);
		if ($dataSource == 'json') {
			$pd = new JsonDataProvidor(null, null, $dsCfg);
		} else if ($dataSource == 'param') {
			$pd = new ParamDataProvidor(null, null, $dsCfg);
		} else if ($dataSource == 'table') {
			$pd = new TableDataProvidor(null, null, $dsCfg);
		} else if ($dataSource == 'text') {
			$pd = new LineDataProvidor(null, null, $dsCfg);
		} else if (is_subclass_of($dataSource, FieldDataProvidor::class)) {
			$pd = new $dataSource(null, null, $dsCfg);
		}

		return $pd->createConfigForm();
	}
}
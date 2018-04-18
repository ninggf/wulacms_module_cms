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
use cms\classes\form\CmsModelForm;
use cms\classes\model\CmsModelTable;
use cms\classes\ModelDoc;
use wulaphp\io\Ajax;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * 内容模型
 * @package cms\model\controllers
 * @acl     m:site/model
 * @accept  cms\classes\form\CmsModelForm
 */
class IndexController extends IFramePageController {
	use JQueryValidatorController;

	public function index() {
		return $this->render();
	}

	public function edit($id = '') {
		$form = new CmsModelForm(true);
		if ($id) {
			$query  = $form->get($id);
			$domain = $query->get(0);
			$form->inflateByData($domain);
		}
		$data['form']  = BootstrapFormRender::v($form);
		$data['id']    = $id;
		$data['rules'] = $form->encodeValidatorRule($this);

		return view($data);
	}

	/**
	 * 保存
	 *
	 * @param string $id
	 *
	 * @return mixed
	 */
	public function savePost($id = '') {
		$form = new CmsModelForm(true);

		$data = $form->inflate();
		try {
			$form->validate();
			$table = new CmsModelTable();
			if ($id) {
				$res = $table->updateModel($id, $data);
			} else {
				$res = $table->newModel($data);
			}
			if ($res) {
				return Ajax::reload('#table', $id ? '修改成功' : '新模型创建成功');
			} else {
				return Ajax::error('操作失败了');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('EditForm', $ve->getErrors());
		}
	}

	public function del($ids) {
		$ids = safe_ids2($ids);
		if (empty($ids)) {
			return Ajax::error('请告诉我要删除哪个模型啊');
		}
		$table  = new CmsModelTable();
		$models = $table->select('refid,name,id')->where(['id IN' => $ids]);
		$db     = $table->db();
		foreach ($models as $m) {
			$doc = ModelDoc::getDoc($m['refid']);
			if ($doc->isNative()) {
				return Ajax::error($m['name'] . '是内置的模型不能删除哦');
			}
			if ($db->select('*')->from('{cms_channel_model}')->where(['model' => $m['id']])->exist('id')) {
				return Ajax::error($m['name'] . '已经配置到栏目，请先从栏目里删除它');
			}
		}

		$rst = $table->deleteModel($ids);
		if ($rst) {
			return Ajax::reload('#table', '你选择的模型已经删除了.');
		} else {
			return Ajax::error('无法删除你选择的这个模型');
		}
	}

	public function data($q = '', $count = '') {
		$data  = [];
		$model = new CmsModelTable();
		$where = ['hidden' => 0];
		if ($q) {
			$where['name LIKE'] = '%' . $q . '%';
		}
		$query = $model->select('*')->alias('MD')->where($where)->page()->sort();
		$cnt   = $model->db()->select(imv('COUNT(*)'))->from('{cms_model_field} AS CMF')->where(['CMF.model' => imv('CmsModel.id')]);
		$query->field($cnt, 'field_total');
		$rows  = $query->toArray();
		$total = '';
		if ($count) {
			$total = $query->total('id');
		}
		$data['rows']  = $rows;
		$data['total'] = $total;

		return view($data);
	}
}
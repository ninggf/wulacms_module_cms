<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\domain\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\classes\form\CmsDomainForm;
use wulaphp\io\Ajax;
use wulaphp\validator\ValidateException;

/**
 * Class IndexController
 * @package cms\domain\controllers
 * @acl     dm:site/page
 */
class IndexController extends IFramePageController {
	public function index() {
		return $this->render();
	}

	public function data($q = '', $count = '') {
		$model = new CmsDomainForm();
		$where = [];
		if ($q) {
			$where['domain LIKE'] = '%' . $q . '%';
		}
		$query = $model->select('*')->where($where)->page()->sort();
		$rows  = $query->toArray();
		$total = '';
		if ($count) {
			$total = $query->total('id');
		}
		$data['rows']  = $rows;
		$data['total'] = $total;

		return view($data);
	}

	public function edit($id = '') {
		$form = new CmsDomainForm(true);
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

	public function savePost($id) {
		$form = new CmsDomainForm(true);
		$data = $form->inflate();
		try {
			$form->validate();
			if ($id) {
				$res = $form->updateDomain($id, $data);
			} else {
				$res = $form->indsertDomain($data);
			}
			if ($res) {
				return Ajax::reload('#core-admin-table', $id ? '修改成功' : '新域名已经成功创建');
			} else {
				return Ajax::error('操作失败了');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('SettingForm', $ve->getErrors());
		}
	}

	public function del($id) {
		if (!$id) {
			return Ajax::error('参数错误啦!哥!');
		}
		$form = new CmsDomainForm();
		$res  = $form->delDomain($id);

		return Ajax::reload('#core-admin-table', $res ? '删除成功' : '删除失败');
	}
}
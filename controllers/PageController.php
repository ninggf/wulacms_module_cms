<?php
/**
 * //                            _ooOoo_
 * //                           o8888888o
 * //                           88" . "88
 * //                           (| -_- |)
 * //                            O\ = /O
 * //                        ____/`---'\____
 * //                      .   ' \\| |// `.
 * //                       / \\||| : |||// \
 * //                     / _||||| -:- |||||- \
 * //                       | | \\\ - /// | |
 * //                     | \_| ''\---/'' | |
 * //                      \ .-\__ `-` ___/-. /
 * //                   ___`. .' /--.--\ `. . __
 * //                ."" '< `.___\_<|>_/___.' >'"".
 * //               | | : `- \`.;`\ _ /`;.`/ - ` : | |
 * //                 \ \ `-. \_ __\ /__ _/ .-` / /
 * //         ======`-.____`-.___\_____/___.-`____.-'======
 * //                            `=---='
 * //
 * //         .............................................
 * //                  佛祖保佑             永无BUG
 * DEC : cms 页面管理
 * User: wangwei
 * Time: 2018/2/5 下午1:59
 */

namespace cms\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\classes\form\CmsDomainForm;
use wulaphp\io\Ajax;

class PageController extends IFramePageController {

	public function domain() {
		$data = [];

		return $this->render($data);
	}

	public function domain_data($type = '', $q = '', $count = '', $pager, $sort) {
		$page      = $pager['page'];
		$page_size = $pager['size'];
		$model     = new CmsDomainForm();
		$where     = ['id >=' => 1];
		if ($q) {
			$where1['domain LIKE'] = '%' . $q . '%';
			$where[]               = $where1;
		}
		$query = $model->select('*')->where($where)->limit(($page - 1) * $page_size, $page_size)->sort($sort['name'], $sort['dir']);
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

	public function save_domain($id) {
		$form = new CmsDomainForm(true);
		$data = $form->inflate();
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

	}

	public function del_domain($id) {
		if (!$id) {
			return Ajax::error('参数错误啦!哥!');
		}
		$form = new CmsDomainForm();
		$res  = $form->delDomain($id);

		return Ajax::reload('#core-admin-table', $res ? '删除成功' : '删除失败');
	}
}
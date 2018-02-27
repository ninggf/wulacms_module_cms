<?php
/**
 * DEC : cms 页面管理
 * User: wangwei
 * Time: 2018/2/5 下午1:59
 */

namespace cms\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\classes\form\CmsDomainForm;
use cms\classes\model\CmsPage;
use cms\classes\model\CmsPageField;
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

	public function channel() {
		$model        = new CmsPage();
		$channels     = $model->getChannelTree();
		$data['tree'] = $channels;
		$files        = find_files(THEME_DIR, '#^.+\.tpl$#', array(), 1);
		foreach ($files as $f) {
			$f1      = str_replace(THEME_DIR, '', $f);
			$tpls [] = array('id' => $f1, 'text' => $f1);
		}
		$data['tpls'] = $tpls;

		return $this->render($data);
	}

	private function formatTree($tree) {
		global $str;
		$options = [];;
		if (!empty($tree)) {
			foreach ($tree as $key => $value) {
				$options[ $value['url'] ] = $value['name'];
				$str_pad                  = $pad = str_pad('&nbsp;&nbsp;|--', ($value['level'] * 24 + 15), '&nbsp;', STR_PAD_LEFT);
				$str                      .= "<option value='{$value['url']}'>{$str_pad}{$value['name']}</option>\n";
				$options['str']           = $str;
				if (isset($value['child'])) {//查询是否有子节点
					$optionsTmp = $this->formatTree($value['child']);
					if (!empty($optionsTmp)) {
						$options = array_merge($options, $optionsTmp);
					}
				}
			}
		}

		return $options;
	}

	public function tree() {
		$model = new CmsPage();
		$tree  = $model->getChannelTree();

		return $tree['menus'];

	}

	public function save_channel() {
		$post = $_POST;
		if (!$post) {
			return Ajax::error('参数不存在');
		}
		$page_info            = [];
		$cms_page_model       = new CmsPage();
		$cms_page_field_model = new CmsPageField();
		//开启事务
		$db = \wulaphp\app\App::db();
		//移动更新
		if ($post['id']) {
			$ppath = $post['ppath'];
			$opath = $post['opath'];

			$path     = $ppath . $post['path'] . '/';
			$up_data  = ['path' => $path, 'url' => $path . 'index.html'];
			$up_data2 = ['update_time' => time(), 'update_uid' => $this->passport->uid, 'model' => 1, 'path' => $path, 'title' => $post['title'], 'title2' => $post['ftitle'], 'keywords' => $post['keyword'], 'description' => $post['description']];
			$result   = $cms_page_model->updatePage($up_data, ['id' => $post['id']]);
			$result   = $result && $cms_page_field_model->updatePageField($up_data2, ['page_id' => $post['id']]);
			//			$p        = explode('/', $opath);
			//			$ar       = $p[ count($p) - 2 ];
			//			$pppath     = substr($opath, 0, -strlen($ar) - 1);
			$result = $result && $cms_page_model->moveNode($path, $opath) && $cms_page_field_model->updateNode($path, $opath);
			if ($result) {
				return Ajax::reload('', '保存成功');
			}
		}
		if ($post['pid'] && !$post['id']) {
			$page_info = $cms_page_model->get_one($post['pid']);
		}
		$path = $page_info['path'] . $post['path'] . '/';

		$db->start();
		$data1 = ['create_time' => time(), 'model' => 1, 'status' => 1, 'path' => $path, 'url' => $path . 'index.html', 'create_uid' => $this->passport->uid];
		$res   = $cms_page_model->add($data1);
		$data2 = ['page_id' => $res, 'update_time' => time(), 'update_uid' => $this->passport->uid, 'model' => 1, 'path' => $path, 'title' => $post['title'], 'title2' => $post['ftitle'], 'keywords' => $post['keyword'], 'description' => $post['description']];
		$res   = $res && $cms_page_field_model->add($data2);
		if ($res) {
			$db->commit();

			return Ajax::reload('', '保存成功');
		} else {
			$db->rollback();

			return Ajax::error('保存失败');
		}
	}

	//获取page_id相关信息
	public function get_page($id) {
		if (!$id) {
			return false;
		}
		//		$opath  = '/b/c/efd/';
		//		$p      = explode('/', $opath);
		//		$o1path = $p[ count($p) - 2 ];
		//		$path   = substr($opath, 0, -strlen($o1path) - 1);
		//		var_dump($path);
		//		exit;
		$cms_page_model = new CmsPage();
		//$a              = $cms_page_model->getChannelTree('d/c/tc/');
		//$cms_page_model->moveNode($a['menus'][0],'la/');
		//		var_dump($a['menus'][0]);
		//		exit;
		$res         = $cms_page_model->get_page($id);
		$path        = explode('/', $res['path']);
		$res['path'] = $path[ count($path) - 2 ];

		return $res;
	}
}
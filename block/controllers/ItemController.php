<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\block\controllers;

use backend\classes\BackendController;
use backend\form\BootstrapFormRender;
use cms\block\form\CmsBlockItemForm;
use cms\block\model\CmsBlock;
use cms\block\model\CmsBlockItem;
use wulaphp\io\Ajax;
use wulaphp\io\Response;
use wulaphp\validator\ValidateException;

/**
 * Class ItemController
 * @package cms\block\controllers
 * @acl     m:site/block
 */
class ItemController extends BackendController {
	public function edit($id, $bid) {
		$bid = intval($bid);
		if (!$bid) {
			Response::error('未知的区块');
		}
		$id   = intval($id);
		$data = ['id' => $id];
		$form = new CmsBlockItemForm();
		if ($id) {
			$form->inflateFromDB(['id' => $id]);
		} else {
			$blockm = new CmsBlock();
			$block  = $blockm->get($bid, 'page,name')->ary();
			if (!$block) {
				Response::error('未知的区块');
			}
			$data['pn']       = $block['page'] . ':' . $block['name'];
			$data['block_id'] = $bid;
			$form->inflateByData($data);
		}
		$data['rules'] = $form->encodeValidatorRule($this);
		$data['form']  = BootstrapFormRender::v($form);

		return view($data);
	}

	public function savePost($id) {
		$form = new CmsBlockItemForm();
		$item = $form->inflate();
		try {
			$form->validate();
			if ($id) {
				$rst = $form->updateItem($item, $this->passport->uid);
			} else {
				unset($item['id']);
				$rst = $form->newItem($item, $this->passport->uid);
			}
			if ($rst) {
				return Ajax::reload('#table', $id ? '条目已经更新' : '新的条目已经添加');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('EditItemForm', $ve->getErrors());
		} catch (\PDOException $pe) {
			return Ajax::error('数据库出错:' . $pe->getMessage());
		}

		return Ajax::error('保存条目时出错了，请联系管理员');
	}

	public function csort($id, $sort) {
		$id   = intval($id);
		$sort = intval($sort);

		$blockItem = new CmsBlockItem();
		$db        = $blockItem->db();

		$db->update('{cms_block_item}')->set(['sort' => $sort])->where(['id' => $id])->exec();

		return Ajax::reload('#table');

	}

	public function del($ids) {
		$ids = safe_ids2($ids);
		if (empty($ids)) {
			return Ajax::warn('没有要删除的条目');
		}
		$blockItem = new CmsBlockItem();
		$rst       = $blockItem->deleteItems($ids);
		if ($rst) {
			return Ajax::reload('#table', '所选条目已经删除');
		} else {
			return Ajax::warn('删除失败啦.');
		}
	}

	public function q($q) {
		$data['more'] = false;
		$blockm       = new CmsBlock();
		if ($q) {
			$results = $blockm->select('pn AS id,pn AS text')->where(['pn %' => "%$q%"])->toArray(null, null, [
				['id' => $q, 'text' => $q]
			]);
		} else {
			$results = $blockm->select('pn AS id,pn AS text')->limit(0, 15)->toArray();
		}
		$data['results'] = $results;

		return $data;
	}

	public function data($blockid = '', $q = '', $count = '') {
		$model = new CmsBlockItem();
		$where = [];
		$data  = [];
		if ($blockid) {
			$where['block_id'] = $blockid;
		}
		if ($q) {
			$where['title %'] = "%$q%";
		}
		$q = $model->select();

		$q->where($where)->page()->sort('pn', 'a')->sort();
		if ($count) {
			$data['total'] = $q->total('id');
		}
		$data['rows']    = $q->toArray();
		$data['canEdit'] = $this->passport->cando('edit:site/block');
		$data['canDel']  = $this->passport->cando('del:site/block');

		return view($data);
	}
}
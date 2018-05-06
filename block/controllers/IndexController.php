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

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\block\form\CmsBlockForm;
use cms\block\model\CmsBlock;
use wulaphp\io\Ajax;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * Class IndexController
 * @package cms\block\controllers
 * @acl     m:site/block
 * @accept  cms\block\form\CmsBlockForm
 */
class IndexController extends IFramePageController {
	use JQueryValidatorController;

	public function index() {
		$blockm          = new CmsBlock();
		$pages           = $blockm->select('page')->groupBy('page')->sort('page', 'a')->toArray();
		$data['pages']   = $pages;
		$blocks          = $blockm->select('id,page,name')->sort('page', 'a')->toArray();
		$data['blocks']  = $blocks;
		$data['canEdit'] = $this->passport->cando('edit:site/block');
		$data['canDel']  = $this->passport->cando('del:site/block');

		return $this->render($data);
	}

	//区块列表
	public function blocks($pn = '') {
		$blockm = new CmsBlock();

		$blocks = $blockm->select('id,page,name')->sort('page', 'a');
		if ($pn) {
			$blocks->where(['page' => $pn]);
		}
		$data['blocks'] = $blocks->toArray();

		return view($data);
	}

	//页面下拉
	public function pages() {
		$blockm        = new CmsBlock();
		$pages         = $blockm->select('page')->groupBy('page')->sort('page', 'a')->toArray();
		$data['pages'] = $pages;

		return view($data);
	}

	/**
	 * @param string $id
	 *
	 * @return \wulaphp\mvc\view\SmartyView
	 * @acl edit:site/block
	 */
	public function edit($id = '') {
		$data = ['id' => $id];
		$form = new CmsBlockForm();
		if ($id) {
			$form->inflateFromDB(['id' => $id]);
		}
		$data['rules'] = $form->encodeValidatorRule($this);
		$data['form']  = BootstrapFormRender::v($form);

		return view($data);
	}

	public function ptip($q) {
		$data['more'] = false;
		$blockm       = new CmsBlock();
		if ($q) {
			$results = $blockm->select('page AS id,page AS text')->groupBy('page')->where(['page %' => "%$q%"])->toArray(null, null, [
				['id' => $q, 'text' => $q]
			]);
		} else {
			$results = $blockm->select('page AS id,page AS text')->groupBy('page')->toArray();
		}
		$data['results'] = $results;

		return $data;
	}

	/**
	 * @param string $id
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 * @acl edit:site/block
	 */
	public function savePost($id = '') {
		$form  = new CmsBlockForm();
		$block = $form->inflate();
		try {
			$form->validate();
			if ($id) {
				$rst = $form->updateBlock($block, $this->passport->uid);
			} else {
				unset($block['id']);
				$rst = $form->newBlock($block, $this->passport->uid);
			}
			if ($rst) {
				return Ajax::success($id ? '区块已经更新' : '新的区块已经添加');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('EditForm', $ve->getErrors());
		} catch (\PDOException $pe) {
			return Ajax::error('数据库出错:' . $pe->getMessage());
		}

		return Ajax::error('保存区块时出错了，请联系管理员');
	}

	/**
	 * @param $id
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 * @acl del:site/block
	 */
	public function del($id) {
		$id = intval($id);
		if (!$id) {
			return Ajax::error('区块ID为空');
		}

		$blockm = new CmsBlock();
		$blockm->deleteBlock($id);

		return Ajax::success('区块已经删除');
	}
}
<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\tag\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\tag\form\CmsTagForm;
use cms\tag\model\CmsTag;
use wulaphp\io\Ajax;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * Class IndexController
 * @package cms\tag\controllers
 * @acl     m:site/tag
 */
class IndexController extends IFramePageController {
	use JQueryValidatorController;

	public function index() {
		$data['canEdit'] = $this->passport->cando('edit:site/tag');
		$data['canDel']  = $this->passport->cando('del:site/tag');
		$data['canDict'] = $this->passport->cando('dict:site/tag');

		return $this->render($data);
	}

	/**
	 * @param $id
	 *
	 * @return \wulaphp\mvc\view\SmartyView
	 * @acl edit:site/tag
	 */
	public function edit($id) {
		$data = ['id' => $id];
		$form = new CmsTagForm();
		if ($id) {
			$form->inflateFromDB(['id' => $id]);
		}
		$data['rules'] = $form->encodeValidatorRule($this);
		$data['form']  = BootstrapFormRender::v($form);

		return view($data);
	}

	/**
	 * @param string $id
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 * @acl edit:site/tag
	 */
	public function savePost($id = '') {
		$form = new CmsTagForm();
		$tag  = $form->inflate();
		try {
			$form->validate();
			if ($id) {
				$rst = $form->updateTag($tag, $this->passport->uid);
			} else {
				unset($tag['id']);
				$rst = $form->newTag($tag, $this->passport->uid);
			}
			if ($rst) {
				return Ajax::reload('#table', $id ? '标签已经更新' : '新的标签已经添加');
			}
		} catch (ValidateException $ve) {
			return Ajax::validate('EditForm', $ve->getErrors());
		} catch (\PDOException $pe) {
			return Ajax::error('数据库出错:' . $pe->getMessage());
		}

		return Ajax::error('保存标签时出错了，请联系管理员');
	}

	public function csort($id, $sort) {
		$id   = intval($id);
		$sort = intval($sort);

		$blockItem = new CmsTag();
		$db        = $blockItem->db();

		$db->update('{cms_tag}')->set(['sort' => $sort])->where(['id' => $id])->exec();

		return Ajax::reload('#table');

	}

	/**
	 * 以逗号分隔的标签ID
	 *
	 * @param string $ids
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 * @acl del:site/tag
	 */
	public function del($ids) {
		$ids = safe_ids2($ids);
		if (!$ids) {
			return Ajax::error('标签ID为空');
		}

		$blockm = new CmsTag();
		$blockm->deleteTags($ids);

		return Ajax::reload('#table', '标签已经删除');
	}

	/**
	 * 生成词典.
	 *
	 * @acl dict:site/tag
	 */
	public function dict() {
		if (!extension_loaded('scws')) {
			return Ajax::warn('请先安装scws分词扩展');
		}
		set_time_limit(0);
		$table = new CmsTag();
		$i     = 0;
		$words = [];
		while (true) {
			$tags = $table->select('tag,sort')->desc('sort')->limit($i * 500, 500)->toArray();
			if (!$tags) {
				break;
			}
			foreach ($tags as $tag) {
				$words[] = "{$tag['tag']}\t{$tag['sort']}\t{$tag['sort']}\tnk";
			}
			$i++;
		}
		@file_put_contents(TMP_PATH . 'tag-dict.txt', implode("\n", $words));

		return Ajax::success('词典生成成功');
	}

	public function data($q = '', $count = '') {
		$table = new CmsTag();
		$data  = [
			'canEdit' => $this->passport->cando('edit:site/tag'),
			'canDel'  => $this->passport->cando('del:site/tag')
		];
		$query = $table->select();
		if ($q) {
			$query->where(['tag %' => "%{$q}%"]);
		}
		$query->sort()->page();
		if ($count) {
			$data['total'] = $query->total('id');
		}
		$data['rows'] = $query->toArray();

		return view($data);
	}
}
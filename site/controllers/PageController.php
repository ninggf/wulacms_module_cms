<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\site\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;
use cms\classes\model\CmsPage;
use cms\classes\ModelDoc;
use wulaphp\app\App;
use wulaphp\io\Ajax;
use wulaphp\io\Response;
use wulaphp\mvc\view\View;
use wulaphp\validator\JQueryValidatorController;
use wulaphp\validator\ValidateException;

/**
 * Class PageController
 * @package cms\site\controllers
 * @acl     m:site/page
 * @accept  *
 */
class PageController extends IFramePageController {
	use JQueryValidatorController;

	/**
	 * 新增页面.
	 *
	 * @param string $model   模型ID
	 * @param string $channel 栏目ID
	 *
	 * @acl edit:site/page
	 * @return \wulaphp\mvc\view\View
	 * @throws
	 */
	public function add($model = '', $channel = '') {
		$modelId = intval($model);
		$channel = intval($channel);
		$db      = App::db();
		if (!$modelId) {
			Response::error('我不认识的模型，你让我哪能办啊?');
		}
		if ($modelId == 1 && !$this->passport->cando('mc:site/page')) {
			Response::error('你无权限管理栏目');
		}
		//顶级栏目只能是『栏目』
		if ($modelId != 1 && !$channel) {
			Response::error('顶级栏目的内容模型只能是『栏目』');
		}
		$model = $db->select('name,refid,id')->from('{cms_model}')->where(['id' => $modelId, 'creatable' => 1])->get();
		if (!$model) {
			Response::error('我不认识的模型，你让我哪能办啊?');
		}
		//栏目
		$channel = $this->getChannel($channel, $db);
		if ($channel['status'] == 2) {
			Response::error('栏目已经放入回收站不可增加内容.');
		}
		//看看这个栏目是否绑定了此模型
		if ($modelId != 1 && $channel['chid'] && !$db->select('*')->from('{cms_channel_model}')->where([
				'model'   => $modelId,
				'page_id' => $channel['chid']
			])->exist('id')) {
			Response::error('栏目『' . $channel['name'] . '』未绑定内容模型『' . $model['name'] . '』');
		}

		$doc = ModelDoc::getDoc($model['refid']);
		if (!$doc) {
			Response::error('内容模型实现不存在。');
		}
		$data = ['cmodel' => $model, 'cchannel' => $channel, 'channel' => $channel['chid'], 'model' => $model['id']];
		//模型自定义编辑模板
		$form = $doc->getForm(0, $data);
		if ($form) {
			//模型自定义表单
			$form->inflateByData($data);
			$data['form'] = BootstrapFormRender::v($form);
		}
		$tpl = $doc->getTpl(0, $data);

		$dform = $doc->getDynamicForm(0);

		if ($dform) {
			//模型的动态表单
			$data['dform'] = BootstrapFormRender::v($dform);
		}
		if ($form) {
			$form->applyRules($dform);
			if (method_exists($form, 'encodeValidatorRule')) {
				$data['validate_rules'] = $form->encodeValidatorRule($this);
			}
		} else if ($dform) {
			if (method_exists($dform, 'encodeValidatorRule')) {
				$data['validate_rules'] = $dform->encodeValidatorRule($this);
			}
		}
		if ($tpl) {
			return $this->render($tpl, $data);
		} else {
			return $this->render('page/edit', $data);
		}
	}

	/**
	 * 编辑页面入口.
	 *
	 * @param string $id  页面ID
	 * @param string $ver 版本
	 *
	 * @acl edit:site/page
	 * @return \wulaphp\mvc\view\View
	 * @throws
	 */
	public function edit($id = '', $ver = '') {
		$id = intval($id);
		if (!$id) {
			Response::error('未知页面编号');
		}
		$table   = new CmsPage();
		$revData = $table->loadRev($id, $ver);
		if (!$revData) {
			//未加载到版本数据时尝试加载原始数据
			$revData = $table->loadFields($id);
		}
		if (!$revData) {
			Response::error('页面不知道飞哪儿去了，换不到鸟!!');
		}
		$modelId = intval($revData['model']);
		$channel = intval($revData['channel']);
		$db      = App::db();
		if (!$modelId) {
			Response::error('我不认识的模型，你让我哪能办啊?');
		}
		if ($modelId == 1 && !$this->passport->cando('mc:site/page')) {
			Response::error('你无权限管理栏目');
		}
		//顶级栏目只能是『栏目』
		if ($modelId != 1 && !$channel) {
			Response::error('顶级栏目的内容模型只能是『栏目』');
		}
		$model = $db->select('name,refid,id')->from('{cms_model}')->where(['id' => $modelId, 'creatable' => 1])->get();
		if (!$model) {
			Response::error('我不认识的模型，你让我哪能办啊?');
		}
		//栏目
		$channel = $this->getChannel($channel, $db);
		if ($channel['status'] == 2) {
			Response::error('栏目已经放入回收站不可编辑内容.');
		}
		//看看这个栏目是否绑定了此模型
		if ($modelId != 1 && $channel['chid'] && !$db->select('*')->from('{cms_channel_model}')->where([
				'model'   => $modelId,
				'page_id' => $channel['chid']
			])->exist('id')) {
			Response::error('栏目『' . $channel['name'] . '』未绑定内容模型『' . $model['name'] . '』');
		}

		$doc = ModelDoc::getDoc($model['refid']);
		if (!$doc) {
			Response::error('内容模型实现不存在。');
		}
		$data = array_merge($revData, ['id' => $id, 'cmodel' => $model, 'cchannel' => $channel]);
		//模型自定义编辑模板
		$form = $doc->getForm($id, $data);
		if ($form) {
			//模型自定义表单
			$form->inflateByData($data);
			$data['form'] = BootstrapFormRender::v($form);
		}
		$tpl = $doc->getTpl($id, $data);

		$dform = $doc->getDynamicForm(0);

		if ($dform) {
			//模型的动态表单
			$dform->inflateByData($data);
			$data['dform'] = BootstrapFormRender::v($dform);
		}
		if ($form) {
			$form->applyRules($dform);
			if (method_exists($form, 'encodeValidatorRule')) {
				$data['validate_rules'] = $form->encodeValidatorRule($this);
			}
		} else if ($dform) {
			if (method_exists($dform, 'encodeValidatorRule')) {
				$data['validate_rules'] = $dform->encodeValidatorRule($this);
			}
		}
		if ($tpl) {
			return $this->render($tpl, $data);
		} else {
			return $this->render('page/edit', $data);
		}
	}

	/**
	 * 保存
	 *
	 * @param string $id
	 * @param string $model
	 * @param string $channel
	 *
	 * @acl edit:site/page
	 * @return \wulaphp\mvc\view\JsonView
	 * @throws
	 */
	public function save($id = '', $model = '', $channel = '') {
		$modelId = intval($model);
		$channel = intval($channel);
		$id      = intval($id);
		$db      = App::db();
		if (!$modelId) {
			Response::error('我不认识的模型，你让我哪能办啊?');
		}
		if ($modelId == 1 && !$this->passport->cando('mc:site/page')) {
			Response::error('你无权限管理栏目');
		}
		//顶级栏目只能是『栏目』
		if ($modelId != 1 && !$channel) {
			Response::error('顶级栏目的内容模型只能是『栏目』');
		}
		$model = $db->select('name,refid,id')->from('{cms_model}')->where(['id' => $modelId, 'creatable' => 1])->get();
		if (!$model) {
			Response::error('我不认识的模型，你让我哪能办啊?');
		}
		//栏目
		$channel = $this->getChannel($channel, $db);
		//看看这个栏目是否绑定了此模型
		if ($modelId != 1 && $channel['chid'] && !$db->select('*')->from('{cms_channel_model}')->where([
				'model'   => $modelId,
				'page_id' => $channel['chid']
			])->exist('id')) {
			Response::error('栏目『' . $channel['name'] . '』未绑定内容模型『' . $model['name'] . '』');
		}

		$doc = ModelDoc::getDoc($model['refid']);
		if (!$doc) {
			Response::error('内容模型实现不存在。');
		}
		$data = ['id' => $id, 'model' => $model, 'channel' => $channel];
		//模型自定义编辑模板
		$form  = $doc->getForm(0, $data);
		$dform = $doc->getDynamicForm(0);
		$ddata = false;
		if ($dform) {
			$ddata = $dform->inflate();
			try {
				if ($ddata) {
					$dform->validate();
				}
			} catch (ValidateException $ve) {
				return Ajax::validate('PageForm', $ve->getErrors());
			}
		}
		if ($form) {
			$cdata = $form->inflate();
			try {
				$form->validate();
				if ($ddata) {
					$data = array_merge($ddata, $cdata, $data);
				} else {
					$data = array_merge($cdata, $data);
				}
			} catch (ValidateException $ve) {
				return Ajax::validate('PageForm', $ve->getErrors());
			} catch (\Exception $e) {
				return Ajax::error($e->getMessage());
			}
		}
		$rst = false;
		if ($data) {
			try {
				$rst = $doc->save($data, $this->passport->uid);
				if (!$rst) {
					$error = $doc->last_error();
					if (is_string($error)) {
						return Ajax::error($error ? $error : '未知错误');
					} else if ($error instanceof View) {
						return $error;
					} else {
						return Ajax::error('未知错误', 'alert');
					}
				}
			} catch (\Exception $e) {
				return Ajax::error($e->getMessage());
			}
		}
		if ($rst && $data['id']) {
			return Ajax::success(['message' => $id ? '保存成功' : '', 'page' => $data, 'isNew' => $id ? 0 : 1]);
		} else {
			return Ajax::error('保存失败[内容模型异常]');
		}
	}

	/**
	 * 删除目录
	 *
	 * @param string $id
	 *
	 * @acl   mc:site/page
	 * @acl   del:site/page
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function delch($id = '') {
		$table = new CmsPage();

		$table->uid = $this->passport->uid;
		$rst        = $table->deletePage($id);
		if ($rst) {
			return Ajax::success('栏目已经放入回收站');
		} else {
			return Ajax::error('栏目无法放入回收站');
		}
	}

	/**
	 * 删除或放入回收站。
	 *
	 * @param string $id
	 * @param string $force
	 *
	 * @acl   del:site/page
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function del($id, $force = '') {
		$table = new CmsPage();

		$table->uid = $this->passport->uid;
		if ($force) {
			$rst = $table->hardDeletePage($id, $error);
		} else {
			$rst = $table->deletePage($id, $error);
		}
		if ($rst) {
			return Ajax::reload('#content-grid', '页面已经放入回收站');
		} else {
			return Ajax::error('页面无法放入回收站:' . ($error ? $error : ''));
		}
	}

	/**
	 * 从回收站还原栏目
	 *
	 * @param string $id
	 *
	 * @acl   mc:site/page
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function restorech($id = '') {
		$table = new CmsPage();

		$table->uid = $this->passport->uid;
		$rst        = $table->restorePage($id);
		if ($rst) {
			return Ajax::success('栏目已从回收站还原');
		} else {
			return Ajax::error('栏目无法从回收站还原');
		}
	}

	/**
	 * 发布。
	 *
	 * @param string|int $id
	 * @param string|int $ver
	 *
	 * @acl   pb:site/page
	 * @acl   mc:site/page
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function publishch($id = '', $ver = '') {
		$table = new CmsPage();
		$db    = $table->db();
		$db->start();
		$table->uid = $this->passport->uid;
		$rst        = $table->useRev($id, $ver, $db, $table->uid);
		if ($rst) {
			$db->commit();

			return Ajax::success('发布成功');
		} else {
			$db->rollback();

			return Ajax::error('发布失败');
		}
	}

	/**
	 * @param                                $id
	 * @param \wulaphp\db\DatabaseConnection $db
	 *
	 * @return mixed
	 */
	private function getChannel($id, $db) {
		if ($id) {
			$channel = $db->select('title2 AS name,page_id AS chid,CPF.path,CP.status')->from('{cms_page_field} AS CPF')->join('{cms_page} AS CP', 'CPF.page_id = CP.id')->where(['page_id' => $id])->get();
		} else {
			$channel['chid']   = 0;
			$channel['name']   = '无';
			$channel['path']   = '/';
			$channel['status'] = 1;
		}

		return $channel;
	}
}
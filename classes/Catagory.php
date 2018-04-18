<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes;

use cms\classes\model\CmsPage;
use wulaphp\app\App;

/**
 * 栏目页
 */
class Catagory extends ModelDoc {
	public function id() {
		return 'catagory';
	}

	public function name() {
		return '栏目';
	}

	public function getForm($id, &$data) {
		$form = new CatagoryForm(true);
		if ($id) {
			$data['template_file2'] = $data['content_file'];
			$data['url']            = $data['page_name'];
		}

		return $form;
	}

	/**
	 * @param array      $data
	 * @param string|int $uid
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function save(&$data, $uid) {
		$id = $data['id'];
		unset($data['id']);
		$page                 = $this->commonData($data);
		$page['content_file'] = $data['template_file2'];
		$page['store_path']   = $data['store_path'];
		$page['display_sort'] = intval($data['display_sort']);
		$page['page_name']    = $data['url'];
		$page['url']          = ltrim($page['path'] . $data['url'], '/');
		//cms_page
		$table      = new CmsPage();
		$table->uid = $uid;
		if (!$id) {
			$page['create_time'] = time();
			$page['create_uid']  = intval($uid);
			//创建新页面
			$id = $table->newPage($page, $this->error);
			if (!$id) {
				return false;
			}
			//添加到cms_channel表
			$ch['page_id']      = $id;
			$ch['store_path']   = $data['store_path'];
			$ch['display_sort'] = intval($data['display_sort']);
			$rst                = $table->db()->insert($ch)->into('{cms_channel}')->exec();
			if (!$rst) {
				$this->error = '无法保存栏目信息';

				return false;
			}
		} else {
			$page['update_time'] = time();
			$page['update_uid']  = intval($uid);
			$page['ver']         = 1;//栏目不使用版本控制
			$rst                 = $table->updatePage($id, $page, $this->error);
			if (!$rst) {
				return false;
			}
		}
		//关联模型
		$this->bindModels($id, $table->db());
		//最后一定要给id赋值
		$data['id'] = $id;

		return true;
	}

	/**
	 * @param array                         $page
	 * @param \wulaphp\router\UrlParsedInfo $info
	 */
	public function load(&$page, $info) {
		try {
			$id = $page['id'];
			$db = App::db();
			$ch = $db->queryOne('SELECT * FROM {cms_channel} WHERE page_id =' . intval($id) . ' LIMIT 0,1');
			if ($ch) {
				$page['store_path']   = $ch['store_path'];
				$page['display_sort'] = $ch['display_sort'];
				$tmp                  = @explode('/', $page['url']);
				$page['page_name']    = @array_pop($tmp);
			}
		} catch (\Exception $e) {

		}
	}

	/**
	 * 放入回收站
	 *
	 * @param array|int|string $id
	 * @param int|string       $uid
	 *
	 * @return bool
	 */
	public function recycle($id, $uid) {
		$db = $this->transDb();
		if (is_array($id)) {
			$page = $id;
		} else {
			$table = new CmsPage($db);
			$page  = $table->loadFields($id, false);
		}
		$path = $page['path'];
		if ($path && $path != '/') {
			$rst = $db->cudx('UPDATE {cms_page} SET origin_status = status, status=2 WHERE status !=2 AND path LIKE %s', $path . '%');

			return $rst;
		}

		return false;
	}

	/**
	 * 从回收站还原
	 *
	 * @param array|int|string $id
	 * @param int|string       $uid
	 *
	 * @return bool
	 */
	public function restore($id, $uid) {
		$db = $this->transDb();
		if (is_array($id)) {
			$page = $id;
		} else {
			$table = new CmsPage($db);
			$page  = $table->loadFields($id, false);
		}
		$path = $page['path'];
		if ($path && $path != '/') {
			$pages = explode('/', trim($path, '/'));
			$npa   = [];
			foreach ($pages as $pa) {
				$npa[] = $pa;
				$np    = '/' . implode('/', $npa) . '/';
				if (!$db->cudx('UPDATE {cms_page} SET status = origin_status WHERE path = %s', $np)) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * 编辑模板
	 *
	 * @param int|string $id
	 * @param array      $data
	 *
	 * @return string
	 */
	public function getTpl($id, &$data) {
		try {
			$db             = App::db();
			$models         = $db->query('SELECT CM.name,CM.id AS mid,CCM.* FROM {cms_model} AS CM LEFT JOIN {cms_channel_model} AS CCM ON (CCM.model = CM.id AND CCM.page_id = ' . $id . ') WHERE CM.creatable = 1 AND CM.id > 1 ORDER BY CM.id DESC');
			$data['models'] = $models;
		} catch (\Exception $e) {
		}

		return 'page/channel';
	}

	/**
	 * 关联栏目与模型
	 *
	 * @param string|int                     $id
	 * @param \wulaphp\db\DatabaseConnection $db
	 */
	private function bindModels($id, $db) {
		$bm = rqst('bm');
		if ($bm) {
			$de             = [];
			$ne             = [];
			$ccm['page_id'] = $id;
			foreach ($bm as $mid => $m) {
				if (isset($m['b']) && $m['b'] == 'on') {
					$ccm['model']          = $mid;
					$ccm['url_pattern']    = $m['url'];
					$ccm['template_file']  = $m['tpl'];
					$ccm['template_file2'] = $m['tpl2'];
					$ccm['enabled']        = isset($m['e']) && $m['e'] == 'on' ? 1 : 0;
					if ($m['id']) {
						$db->update('{cms_channel_model}')->set($ccm)->where(['id' => $m['id']])->exec();
					} else {
						$ne[] = $ccm;
					}
				} else {
					$de[] = $mid;
				}
			}
			if ($de) {//删除取消关联的模型
				$db->cud('DELETE FROM {cms_channel_model} WHERE page_id = ' . $id . ' AND model IN (' . implode(',', $de) . ')');
			}
			if ($ne) {
				$db->inserts($ne)->into('{cms_channel_model}')->exec();
			}
		}
	}

	/**
	 * 栏目发布时，更新栏目排序.
	 *
	 * @param array                          $data
	 * @param \wulaphp\db\DatabaseConnection $db
	 *
	 * @bind cms\onCatagoryPagePublished
	 * @throws
	 */
	public static function cmsOnCatagoryPagePublished($data, $db) {
		$ch['display_sort'] = intval($data['display_sort']);
		$rst                = $db->update('{cms_channel}')->set($ch)->where(['page_id' => $data['id']])->exec();
		if (!$rst) {
			throw_exception('无法更新栏目信息');
		}
	}
}
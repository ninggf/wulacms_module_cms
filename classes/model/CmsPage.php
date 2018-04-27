<?php

namespace cms\classes\model;

use cms\classes\ModelDoc;
use cms\classes\Pinyin;
use wula\cms\Storage;
use wulaphp\app\App;
use wulaphp\db\DatabaseConnection;
use wulaphp\db\Table;
use wulaphp\io\Ajax;
use wulaphp\io\Request;

class CmsPage extends Table {
	public $uid = 0;

	/**
	 * 加载页面
	 *
	 * @param int|string                    $urlkey
	 * @param \wulaphp\router\UrlParsedInfo $pageInfo 页码
	 *
	 * @return array
	 */
	public function load($urlkey, $pageInfo = null) {
		$db   = $this->dbconnection;
		$sql  = <<<SQL
SELECT 
	CP.id,CP.status,CP.expire,CP.path,CP.ver,
    CPF.*,
    CPV.data_file,
    CM.name AS model_name,CM.refid AS model_id,CM.flags AS model_flags,
	CCH.title2 AS channel_name,CCH.title AS channel_title,
    CCHM.template_file AS default_page_tpl,
    CCHM.template_file2 AS list_page_tpl
FROM {cms_router} AS CR 
LEFT JOIN {cms_page} AS CP ON CP.id = CR.id
LEFT JOIN {cms_page_field} AS CPF ON CP.id = CPF.page_id
LEFT JOIN {cms_model} AS CM ON CP.model = CM.id 
LEFT JOIN {cms_page_field} AS CCH ON CPF.channel = CCH.page_id
LEFT JOIN {cms_channel_model} AS CCHM ON (CP.model = CCHM.model AND CCHM.page_id = CPF.channel)
LEFT JOIN {cms_page_rev} AS CPV ON (CR.id = CPV.page_id AND CPV.ver = CP.ver)
WHERE route = '$urlkey' AND CP.status = 1 LIMIT 0,1
SQL;
		$page = $db->queryOne($sql);
		if (!$page) {
			return null;
		}
		if ($page['data_file']) {
			$fields = $this->loadFile($page['data_file']);
			if ($fields) {
				$page = array_merge($fields, $page);
			}
		}
		//加载页面扩展内容
		$this->loadAddon($page, $pageInfo);

		return $page;
	}

	/**
	 * 加载页面扩展内容.
	 *
	 * @param array                         $page     至少包括id与model字段。
	 * @param \wulaphp\router\UrlParsedInfo $pageInfo 页码
	 */
	public function loadAddon(&$page, $pageInfo) {
		if (isset($page['model_id']) && $page['model_id']) {
			$doc = ModelDoc::getDoc($page['model_id']);
			if ($doc) {
				$doc->load($page, $pageInfo);
			}
		}
	}

	/**
	 * 根据页面ID加载页面基本数据.
	 *
	 * @param string|int $id    页面ID
	 * @param bool       $addon 是否加载更多数据
	 *
	 * @return array
	 */
	public function loadFields($id, $addon = true) {
		$pages = $this->dbconnection->queryOne('SELECT CPF.*,CPF.page_id AS id,CM.name AS model_name,CM.refid AS model_id,CP.status,CP.expire,CP.path,CP.url,CP.noindex FROM {cms_page_field} AS CPF LEFT JOIN {cms_model} AS CM ON CM.id = CPF.model LEFT JOIN {cms_page} AS CP ON CP.id= CPF.page_id WHERE page_id = ' . intval($id) . ' LIMIT 0,1');
		if ($pages && $addon) {
			$this->loadAddon($pages, null);
		}

		return $pages;
	}

	/**
	 * 创建一个新的页面.
	 *
	 * @param array $data  新页面数据
	 * @param mixed $error 错误信息
	 *
	 * @return bool|int 新页面ID或false.
	 */
	public function newPage(&$data, &$error = null) {
		if (!$data) {
			$error = '数据为空';

			return false;
		}
		if (!$data['create_time']) {
			$data['create_time'] = time();
		}
		if (!$data['create_uid']) {
			$data['create_uid'] = $this->uid;
		}
		$data['update_uid']  = $data['create_uid'];
		$data['update_time'] = $data['create_time'];
		$page                = array_filter($data, function ($key) {
			return in_array($key, ['model', 'noindex', 'expire', 'create_time', 'create_uid', 'path', 'url']);
		}, ARRAY_FILTER_USE_KEY);

		if (!$page || empty($page['url']) || empty($page['model']) || empty($page['path'])) {
			$error = '页面数据不完整';

			return false;
		}

		try {
			$this->dbconnection->start();
			$page['ver']    = 0;
			$page['status'] = 0;
			$id             = $this->insert($page);
			if ($id) {
				//创建路由
				$rt['id']   = $id;
				$page['id'] = $id;
				try {
					if (strpos($page['url'], '}')) {//解析URL。
						$data['url'] = $page['url'] = $this->parseURL($page, $data);
						$this->update(['url' => $data['url']], $id);
					}

					$rt['route'] = md5($page['url']);
					$rst         = $this->dbconnection->insert($rt)->into('{cms_router}')->exec();
				} catch (\Exception $ee) {
					$rst = false;
				}
				if (!$rst) {
					$error = Ajax::validate('PageForm', ['url' => '文件名好像已经存在了']);
					$this->dbconnection->rollback();

					return false;
				}
				$data['id'] = $id;

				//创建页面字段
				$fields                = array_filter($data, function ($key) {
					return in_array($key, [
						'channel',
						'model',
						'path',
						'title',
						'title2',
						'keywords',
						'description',
						'template_file',
						'content_file',
						'image',
						'related_pages',
						'author',
						'source',
						'tags',
						'flags'
					]);
				}, ARRAY_FILTER_USE_KEY);
				$fields['page_id']     = $id;
				$fields['update_time'] = $page['create_time'];
				$fields['update_uid']  = $page['create_uid'];
				$rst                   = $this->dbconnection->insert($fields)->into('{cms_page_field}')->exec();
				if (!$rst) {
					throw_exception('无法创建页面[数据库出错]');
				}
				//通知页面创建了
				fire('cms\on' . ucfirst($data['model_refid']) . 'PageCreated', $data, $this->dbconnection);
				fire('cms\onPageCreated', $data, $this->dbconnection);
				//创建版本
				$rst = $this->newRev($id, $data, $this->dbconnection);
				if (!$rst) {
					throw_exception('无法创建页面版本[数据库出错]');
				}
				$data['ver'] = $rst;
				//提交事务
				if ($this->dbconnection->commit()) {
					return $id;
				}
			}

			$error = '数据库出错';
			$this->dbconnection->rollback();

			return false;
		} catch (\Exception $e) {
			$error = $e->getMessage();
			$this->dbconnection->rollback();

			return false;
		}
	}

	/**
	 * 更新页面
	 *
	 * @param string|int $id
	 * @param array      $data
	 * @param mixed      $error
	 *
	 * @return bool
	 */
	public function updatePage($id, &$data, &$error = null) {
		$id = intval($id);
		if (!$id) {
			$error = '未指定要更新的页面';

			return false;
		}
		if (!$data) {
			$error = '数据为空';

			return false;
		}
		$data['id'] = $id;
		try {
			$this->dbconnection->start();
			//创建版本
			$rst = $this->newRev($id, $data, $this->dbconnection);
			if (!$rst) {
				throw_exception('无法创建页面版本[数据库出错]');

				return false;
			}
			$data['ver'] = $rst;
			//提交事务
			if ($this->dbconnection->commit()) {
				return true;
			}
			$error = '数据库出错';

			return false;
		} catch (\Exception $e) {
			$error = $e->getMessage();
			$this->dbconnection->rollback();

			return false;
		}
	}

	/**
	 * 将页面放入回收站.
	 *
	 * @param string|int $id
	 * @param string|int $ver
	 * @param mixed      $error
	 *
	 * @return bool
	 */
	public function deletePage($id, $ver, &$error = null) {
		$id = intval($id);
		if (!$id) {
			$error = '页面ID为空';

			return false;
		}

		$rst = $this->trans(function (DatabaseConnection $dbx) use ($id, $ver) {
			if ($ver) {
				//仅删除版本
				if ($dbx->delete()->from('{cms_page_rev}')->where(['page_id' => $id, 'ver' => $ver])->exec()) {
					$file = 'c' . ($id % 10) . '/data@' . $id . '.' . $ver;
					$this->deleteFile($file);
					fire('cms\onPageRevDeleted', $id, $ver);

					return true;
				}

				return false;
			}
			//放入回收站
			$page = $this->loadFields($id, false);
			if (isset($page['model_id']) && $page['model_id']) {
				$doc = ModelDoc::getDoc($page['model_id']);
				if (!$doc) {
					throw_exception('内容模型实现不存在');
				}
				$doc->transDb($dbx);
				$rst = $doc->recycle($page, $this->uid);
				if (!$rst) {
					throw_exception($doc->last_error());
				}
				fire('cms\onPageRecycled', $id);
			} else {
				throw_exception('内容模型不存在');
			}

			return true;
		});
		if (!$rst) {
			$error = $this->errors;
		}

		return $rst;
	}

	/**
	 * 永久删除页面。
	 *
	 * @param string|int $id
	 * @param null       $error
	 *
	 * @return bool|mixed|null
	 */
	public function hardDeletePage($id, &$error = null) {
		$id = intval($id);
		if (!$id) {
			$error = '页面ID为空';

			return false;
		}
		$rst = $this->trans(function (DatabaseConnection $dbx) use ($id) {
			$page = $this->loadFields($id, false);
			if (isset($page['model_id']) && $page['model_id']) {
				$doc = ModelDoc::getDoc($page['model_id']);
				if (!$doc) {
					throw_exception('内容模型实现不存在');
				}
				$doc->transDb($dbx);
				$rst = $doc->delete($page, $this->uid);
				if (!$rst) {
					throw_exception($doc->last_error());
				}
				fire('cms\onPageDeleted', $id);
			} else {
				throw_exception('内容模型不存在');
			}

			return true;
		});
		if (!$rst) {
			$error = $this->errors;
		}

		return $rst;
	}

	/**
	 * 从回收站还原页面。
	 *
	 * @param string|int $id
	 * @param mixed      $error
	 *
	 * @return bool|mixed|null
	 */
	public function restorePage($id, &$error = null) {
		$id = intval($id);
		if (!$id) {
			$error = '页面ID为空';

			return false;
		}
		$rst = $this->trans(function (DatabaseConnection $dbx) use ($id) {
			$rtn = $dbx->cudx('UPDATE {cms_page} SET status = origin_status WHERE id = ' . $id);
			if (!$rtn) {
				throw_exception('无法更新数据库');
			}
			$page = $this->loadFields($id, false);
			if (isset($page['model_id']) && $page['model_id']) {
				$doc = ModelDoc::getDoc($page['model_id']);
				if (!$doc) {
					throw_exception('内容模型实现不存在');
				}
				$doc->transDb($dbx);
				$rst = $doc->restore($page, $this->uid);
				if (!$rst) {
					throw_exception($doc->last_error());
				}
				fire('cms\onPageRestored', $id);
			} else {
				throw_exception('内容模型不存在');
			}

			return true;
		});
		if (!$rst) {
			$error = $this->errors;
		}

		return $rst;
	}

	/**
	 * 下线操作
	 *
	 * @param array       $ids
	 * @param string|null $error
	 *
	 * @return bool
	 */
	public function unpublish($ids, &$error = null) {
		if (!$ids) {
			$error = '页面ID为空';

			return false;
		}

		$rst = $this->trans(function (DatabaseConnection $db) use ($ids) {
			//将cms_page的status改为0
			$idx = '(' . implode(',', $ids) . ')';
			$rst = $db->cudx('UPDATE {cms_page} SET status = 0,origin_status=0 WHERE status =1 AND id IN ' . $idx);
			//将cms_page_field的status改为0
			$rst = $rst && $db->cudx('UPDATE {cms_page_field} SET status = 0 WHERE status =1 AND page_id IN ' . $idx);
			//将cms_page_rev的status改为0
			$rst = $rst && $db->cudx('UPDATE {cms_page_rev} AS CPR, {cms_page} AS CP SET CPR.status = 0 WHERE CPR.page_id = CP.id AND CPR.ver = CP.ver AND CPR.page_id IN ' . $idx);

			return $rst;
		});
		if (!$rst) {
			$error = $this->errors;
		}

		return $rst;
	}

	/**
	 * 送审
	 *
	 * @param array $ids
	 * @param null  $error
	 *
	 * @return bool
	 */
	public function pendingApprove($ids, &$error = null) {
		if (!$ids) {
			$error = '页面ID为空';

			return false;
		}

		$rst = $this->trans(function (DatabaseConnection $db) use ($ids) {
			$idx = '(' . implode(',', $ids) . ')';
			//将cms_page_rev的status改为0
			$rst = $db->cudx('UPDATE {cms_page_rev} SET status = 1 WHERE status = 0 AND page_id IN ' . $idx);

			return $rst;
		});
		if (!$rst) {
			$error = $this->errors;
		}

		return $rst;
	}

	/**
	 * 驳回，审核不通过
	 *
	 * @param array       $ids
	 * @param string|null $error
	 *
	 * @return bool|mixed|null
	 */
	public function notApprove($ids, &$error = null) {
		if (!$ids) {
			$error = '页面ID为空';

			return false;
		}

		$rst = $this->trans(function (DatabaseConnection $db) use ($ids) {

			$idx = '(' . implode(',', $ids) . ')';
			//将cms_page_rev的status改为2
			$rst = $db->cudx('UPDATE {cms_page_rev} SET status = 2 WHERE status = 1 AND page_id IN ' . $idx);

			return $rst;
		});
		if (!$rst) {
			$error = $this->errors;
		}

		return $rst;
	}

	/**
	 * 创建一个新的版本.
	 *
	 * @param string|int                     $id   页面ID
	 * @param array                          $data 页面数据
	 * @param \wulaphp\db\DatabaseConnection $db   数据库连接
	 *
	 * @return bool|int 版本号或false.
	 */
	public function newRev($id, $data, $db = null) {
		$fields = array_filter($data, function ($key) {
			return in_array($key, [
				'ver',
				'update_time',
				'update_uid',
				'content_file',
				'channel',
				'model',
				'path',
				'title',
				'title2',
				'keywords',
				'description',
				'template_file',
				'image',
				'related_pages',
				'author',
				'source',
				'tags',
				'flags'
			]);
		}, ARRAY_FILTER_USE_KEY);
		if (!$fields['update_uid']) {
			$data['update_uid'] = $fields['update_uid'] = $this->uid;
		}
		if ($fields['update_time']) {
			$data['update_time'] = $fields['update_time'] = time();
		}
		unset($data['path']);//path是动态生成的，不保存.
		$fields['page_id'] = $id;
		$db                = $db ? $db : $this->dbconnection;
		//开启审核机制
		$approveEnabled = App::bcfg('approveEnabled@cms', false);
		//开启版本控制
		$vcEnabled        = App::bcfg('vcEnabled@cms', false);
		$fields['status'] = $approveEnabled ? 0 : 3;
		$fields['ip']     = Request::getIp();
		$newVer           = false;
		if (!isset($data['ver']) || !$data['ver']) {
			$nv = $db->queryOne('SELECT MAX(ver) AS new_ver FROM {cms_page_rev} WHERE page_id = ' . $id);
			if ($nv && $nv['new_ver']) {
				if ($vcEnabled) {//开启版本控制
					$nv     = $nv['new_ver'] + 1;
					$newVer = true;
				} else {
					$nv = $nv['new_ver'];
				}
			} else {
				$nv     = 1;
				$newVer = true;
			}
			$fields['ver'] = $nv;
		} else {
			$fields['ver'] = $data['ver'];
		}
		//版本数据存储路径
		$file = 'c' . ($id % 10) . '/data@' . $id . '.' . $fields['ver'];
		if (!$this->store($file, $data)) {
			return false;
		}
		$fields['data_file'] = $file;
		if ($newVer) {
			try {
				$rst = $db->insert($fields)->into('{cms_page_rev}')->exec();
			} catch (\Exception $e) {
				$fields['update_time'] = time();
				$fields['update_uid']  = $data['update_uid'] ? $data['update_uid'] : $this->uid;
				$rst                   = $db->update('{cms_page_rev}')->set($fields)->where([
					'page_id' => $id,
					'ver'     => $fields['ver']
				])->exec(true);
			}
		} else {
			$fields['update_time'] = time();
			$fields['update_uid']  = $data['update_uid'] ? $data['update_uid'] : $this->uid;
			$rst                   = $db->update('{cms_page_rev}')->set($fields)->where([
				'page_id' => $id,
				'ver'     => $fields['ver']
			])->exec(true);

			if (!$rst) {
				$rst = $db->insert($fields)->into('{cms_page_rev}')->exec();
			}
		}
		if (!$rst) {
			//修改版本数据异常
			return false;
		}
		try {
			//删除多余的已经发布版本
			$maxVers = App::icfgn('maxVer@cms', 5);
			if ($maxVers <= 0) {
				$maxVers = 2;
			}
			$ps3 = $db->query('SELECT ver,data_file FROM {cms_page_rev} WHERE status = 3 AND page_id = %d ORDER BY ver DESC LIMIT %d,100', $id, $maxVers);
			if ($ps3) {
				$vers = [];
				foreach ($ps3 as $p) {
					$this->deleteFile($p['data_file']);
					$vers[] = $p['ver'];
				}
				$db->delete()->from('{cms_page_rev}')->where(['page_id' => $id, 'ver IN' => $vers])->exec();
			}
			//删除多余的草稿版本
			$ps2 = $db->query('SELECT ver,data_file FROM {cms_page_rev} WHERE status IN (0,2) AND page_id = %d ORDER BY ver DESC LIMIT %d,100', $id, $maxVers);
			if ($ps2) {
				$vers = [];
				foreach ($ps2 as $p) {
					$this->deleteFile($p['data_file']);
					$vers[] = $p['ver'];
				}
				$db->delete()->from('{cms_page_rev}')->where(['page_id' => $id, 'ver IN' => $vers])->exec();
			}
			fire('cms\on' . ucfirst($data['model_refid'] . 'PageUpdated'), $data, $data);
			fire('cms\onPageUpdated', $data, $db);
		} catch (\Exception $e) {
			return false;
		}
		if (!$approveEnabled) {
			//未开启审核机制，直接发布
			$rst = $this->useRev($id, $fields['ver'], $db);
			if (!$rst) {
				return false;
			}
		}

		return $fields['ver'];
	}

	/**
	 * 使用版本.
	 *
	 * @param string|int                     $id
	 * @param string|int                     $ver
	 * @param \wulaphp\db\DatabaseConnection $db
	 * @param string|int                     $uid
	 *
	 * @return bool|int
	 */
	public function useRev($id, $ver, $db = null, $uid = null) {
		$id  = intval($id);
		$ver = intval($ver);
		if (!$ver || !$id) {
			return false;
		}
		$db   = $db ? $db : $this->dbconnection;
		$data = $this->loadRev($id, $ver);
		if (!$data) {
			return false;
		}
		//创建页面字段
		$fields = array_filter($data, function ($key) {
			return in_array($key, [
				'channel',
				'model',
				'path',
				'title',
				'title2',
				'keywords',
				'description',
				'template_file',
				'content_file',
				'image',
				'image1',
				'image2',
				'view',
				'cmts',
				'dig',
				'dig1',
				'related_pages',
				'author',
				'source',
				'tags',
				'flags'
			]);
		}, ARRAY_FILTER_USE_KEY);

		$fields['update_time'] = time();
		$fields['update_uid']  = $uid ? $uid : (isset($data['update_uid']) ? $data['update_uid'] : $this->uid);
		$fields['status']      = 1;//发布状态
		//更新cms_page_field
		if ($db->update('{cms_page_field}')->set($fields)->where(['page_id' => $id])->exec()) {
			//更新cms_page
			if ($db->update('{cms_page}')->set([
				'ver'     => $data['ver'],
				'status'  => 1,
				'expire'  => intval($data['expire']),
				'noindex' => intval($data['noindex'])
			])->where(['id' => $id])->exec()) {
				//发布
				return $this->publishPage($id, $data, $db, $uid ? $uid : $fields['update_uid']);
			}
		}

		return false;
	}

	/**
	 * 加载版本数据
	 *
	 * @param int|string      $id  页面ID
	 * @param string|int|null $ver 版本
	 *
	 * @return array
	 */
	public function loadRev($id, $ver = null) {
		$id = intval($id);
		if (!$id) {
			return [];
		}
		$ver = intval($ver);
		$sql = 'SELECT CPV.* FROM {cms_page_rev} AS CPV WHERE CPV.page_id = ' . $id;
		if ($ver) {
			$sql .= ' AND CPV.ver = ' . $ver;
		} else {
			$sql .= ' ORDER BY ver DESC';
		}
		$sql  .= ' LIMIT 0,1';
		$data = $this->dbconnection->queryOne($sql);
		if (!$data) {
			return [];
		}
		$data_file = $data['data_file'];
		$pageData  = $this->loadFile($data_file);
		if ($pageData) {
			$pageData['ver'] = $data['ver'];
		}

		return $pageData;
	}

	/**
	 * 存一个文件.
	 *
	 * @param string       $file
	 * @param array|string $data
	 *
	 * @return bool
	 */
	public function store($file, $data) {
		try {
			$ssns    = App::cfg('storage@cms', 'file:path=storage');
			$storage = new Storage($ssns);
			if (is_array($data)) {
				$data = json_encode($data);
			}

			return $storage->save($file, $data);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * 删除存储的文件.
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	public function deleteFile($file) {
		try {
			$ssns    = App::cfg('storage@cms', 'file:path=storage');
			$storage = new Storage($ssns);

			return $storage->delete($file);
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * 从存储加载文件内容
	 *
	 * @param string $file 文件名.
	 * @param bool   $json 是否转换为json数据.
	 *
	 * @return array|string
	 */
	public function loadFile($file, $json = true) {
		if (!$file) {
			return $json ? [] : '';
		}
		try {
			$ssns    = App::cfg('storage@cms', 'file:path=storage');
			$storage = new Storage($ssns);
			$cnt     = $storage->load($file);
			if ($cnt && $json) {
				$data = @json_decode($cnt, true);

				return $data ? $data : [];
			}

			return $cnt ? $cnt : '';
		} catch (\Exception $e) {
			return $json ? [] : '';
		}
	}

	/**
	 * @param $np
	 * @param $opath
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function moveNode($np, $opath) {
		$len          = strlen($opath) + 1;
		$data['path'] = imv("CONCAT('$np',SUBSTR(path,$len))")->noquote();
		try {
			$this->update($data, ['path LIKE' => $opath . '%', 'status' => 1]);
		} catch (\Exception $e) {
			throw_exception($e->getMessage());
		}

		return true;
	}

	private function parseURL($page, $data) {
		$arg = [
			'aid'         => $page['id'],
			'tid'         => $data ['channel'],
			'model'       => $data ['model_refid'],
			'mid'         => $data ['model'],
			'create_time' => $page ['create_time'],
			'path'        => trim($data ['path'], '/'),
			'title'       => $data ['title']
		];

		return $page['url'] = $this->parse_page_url($page['url'], $arg);
	}

	private function parse_page_url($pattern, $data) {
		static $ps = [
			'{aid}',//0
			'{Y}',//1
			'{M}',//2
			'{D}',//3
			'{cc}',//4
			'{tid}',//5
			'{model}',//6
			'{mid}',//7
			'{path}',//8
			'{rpath}',//9
			'{title}',//10
			'{py}'//11
		];
		$pattern = @preg_replace('/\s+/', '-', $pattern);
		$r [0]   = isset ($data ['aid']) ? $data ['aid'] : 0;

		if (isset ($data ['create_time']) && $data['create_time']) {
			$time = $data ['create_time'];
		} else {
			$time = time();
		}
		$r [1] = date('Y', $time);
		$r [2] = date('m', $time);
		$r [3] = date('d', $time);

		$r [4] = base_convert($r[0], 10, 36);
		$r [5] = isset ($data ['tid']) ? $data ['tid'] : 0;
		$r [6] = isset ($data ['model']) ? $data ['model'] : 'page';
		$r [7] = isset ($data ['mid']) ? $data ['mid'] : '0';

		if (isset ($data ['path']) && !empty ($data ['path'])) {
			$r [8] = trim($data ['path'], '/');
			$paths = explode('/', $r [8]);
			array_shift($paths);
			$r [9] = implode('/', $paths);
		} else {
			$r [8] = '';
			$r [9] = '';
		}
		$r [10] = isset ($data ['title']) ? preg_replace('/\s+/', '-', $data ['title']) : '';
		$r[11]  = Pinyin::c($r[10]);

		return ltrim(str_replace($ps, $r, $pattern), '/');
	}

	/**
	 * 发布页面
	 *
	 * @param string|int                     $id   页面ID
	 * @param array                          $data 页面数据
	 * @param \wulaphp\db\DatabaseConnection $db
	 * @param string|int                     $uid
	 *
	 * @return bool
	 */
	private function publishPage($id, $data, $db = null, $uid = 0) {
		$db = $db ? $db : $this->dbconnection;
		if (!$data || !$id) {
			return false;
		}
		try {
			//更新tags
			if (!$db->cudx('DELETE FROM {cms_page_tag} WHERE page_id = %d', $id)) {
				return false;
			}
			if (isset($data['tags']) && $data['tags']) {
				$tags = @preg_split('/[\s,|，]+/u', trim($data['tags']));
				if ($tags) {
					$tagDatas     = [];
					$t['page_id'] = $id;
					foreach ($tags as $tag) {
						$t['tag']   = $tag;
						$tagDatas[] = $t;
					}
					$db->inserts($tagDatas)->into('{cms_page_tag}')->exec();
				}
			}
			//更新flags
			if (!$db->cudx('DELETE FROM {cms_page_flag} WHERE page_id = %d', $id)) {
				return false;
			}

			if (isset($data['flags']) && $data['flags']) {
				$flags = @explode(',', $data['flags']);
				if ($flags) {
					$tagDatas     = [];
					$f['page_id'] = $id;
					foreach ($flags as $tag) {
						$f['flag']  = $tag;
						$tagDatas[] = $f;
					}
					$db->inserts($tagDatas)->into('{cms_page_flag}')->exec();
				}
			}

			//更新URL
			if (strpos($data['url'], '}')) {
				$data['url'] = $this->parseURL($data, $data);
			}

			$key = md5($data['url']);
			$rt  = $db->queryOne('SELECT route FROM {cms_router} WHERE id=' . $id);
			if ($rt) {
				if ($rt['route'] != $key) {
					//更新路由
					if (!$db->cudx('UPDATE {cms_router} SET route = %s WHERE id = %d', $key, $id)) {
						return false;
					}
					//更新页面URL
					if (!$db->cudx('UPDATE {cms_page} SET url = %s WHERE id = %d', $data['url'], $id)) {
						return false;
					}
				}
			} else {
				//更新页面URL
				if (!$db->cudx('UPDATE {cms_page} SET url = %s WHERE id = %d', $data['url'], $id)) {
					return false;
				}
				if (!$db->cud('INSERT INTO {cms_router}(route,id) VALUES(%s,%d)', $key, $id)) {
					return false;
				}
			}
			$pub_time = time();
			if ($data['publish_day']) {
				$pt = $data['publish_day'];
				if ($data['publish_hm']) {
					$pt .= ' ' . $data['publish_hm'];
				}
				$pub_time1 = @strtotime($pt);
				if ($pub_time1) {
					$pub_time = $pub_time1;
				}
			}

			//更新版本为已发布状态
			if (!$db->cudx('UPDATE {cms_page_rev} SET status = 3,publisher=' . intval($uid) . ',publish_time=' . $pub_time . ' WHERE page_id = %d', $id)) {
				return false;
			}
			//更新版本为已发布状态
			if (!$db->cudx('UPDATE {cms_page_field} SET publisher=' . intval($uid) . ',publish_time=' . $pub_time . ' WHERE page_id = %d', $id)) {
				return false;
			}
			//通知页面发布了
			fire('cms\on' . ucfirst($data['model_refid']) . 'PagePublished', $data, $db);
			fire('cms\onPagePublished', $data, $db);

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}
}
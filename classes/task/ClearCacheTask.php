<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\task;

use system\classes\Task;
use wulaphp\app\App;
use wulaphp\cache\Cache;
use wulaphp\form\FormTable;

class ClearCacheTask extends Task {
	public function run() {
		$opts = $this->options;
		try {
			if (isset($opts['page_id'])) {
				$cacher = Cache::getCache();
				$db     = App::db();
				$limit  = 500;
				if ($opts['page_id']) {
					$ids = safe_ids2($opts['page_id']);
					if ($ids) {
						$len = count($ids);

						foreach ($ids as $c => $id) {
							//取path
							$pg = $db->queryOne('select path from {cms_page} WHERE id =%d and model =1', $id);
							if ($pg) {
								$start = 0;
								$path  = $pg['path'] . '%';
								$this->log('正在清空' . $pg['path'] . '目录下的页面缓存...');
								$cnt = 0;
								while (true) {
									$keys = $db->query('SELECT cid FROM {cms_cache} AS CC LEFT JOIN {cms_page} AS CP ON CP.id = CC.page_id WHERE CP.path LIKE %s ORDER BY cid ASC LIMIT %d,%d', $path, $start, $limit);
									if (!$keys) {
										break;
									}
									foreach ($keys as $key) {
										$cacher->delete($key['cid']);
										$cnt++;
									}
									$start += $limit;
								}
								$this->log($pg['path'] . '目录下的页面缓存已清空，共删除缓存:' . $cnt . '个');
							} else {
								$cnt              = 0;
								$where['page_id'] = $id;
								$keys             = $db->select('cid')->from('{cms_cache}')->where($where);
								foreach ($keys as $key) {
									$cacher->delete($key['cid']);
									$cnt++;
								}
								$this->log('共清空页面' . $id . $cnt . '个缓存');
							}
							$this->update(ceil($c / $len * 100));
						}
					}
				} else {
					$where['page_id'] = 0;

					$keys = $db->select('cid')->from('{cms_cache}')->where($where);
					$cnt  = 0;
					foreach ($keys as $key) {
						$cacher->delete($key['cid']);
						$cnt++;
					}
					$this->log('共清空首页和其它页面' . $cnt . '个缓存');
				}
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}

		return true;
	}

	public function getForm() {
		return new CCTForm(true);
	}
}

class CCTForm extends FormTable {
	public $table = null;
	/**
	 * 内容ID
	 * @var \backend\form\TextField
	 * @type string
	 * @note 多个ID以逗号分隔,如:1,3.不填写则删除首页与路由页缓存
	 */
	public $page_id = 0;
}
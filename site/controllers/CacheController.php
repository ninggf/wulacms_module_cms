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
use system\task\TaskQueue;
use wulaphp\app\App;
use wulaphp\cache\Cache;
use wulaphp\io\Ajax;

/**
 * Class CacheController
 * @package cms\site\controllers
 * @acl     hc:site/page
 */
class CacheController extends IFramePageController {
	/**
	 * 清空页面缓存
	 *
	 * @param string $ids
	 * @param string $type 为1时清空栏目下所有页面的缓存.
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 * @throws
	 */
	public function clear($ids, $type = '') {
		$cacher = Cache::getCache();
		if ($ids) {
			$ids = safe_ids2($ids);
			if ($type) {//清空全部缓存
				$tq  = new TaskQueue();
				$ids = implode(',', $ids);
				$tq->newTask('删除栏目缓存:' . $ids, 'cms\classes\task\ClearCacheTask', 'P', 0, 0, ['page_id' => $ids]);

				return Ajax::success('任务已加入队列');
			} else {
				$where['page_id IN'] = $ids;
				$db                  = App::db();
				$keys                = $db->select('cid')->from('{cms_cache}')->where($where);
				foreach ($keys as $key) {
					$cacher->delete($key['cid']);
				}
			}
		} else {
			$where['page_id'] = 0;
			$db               = App::db();
			$keys             = $db->select('cid')->from('{cms_cache}')->where($where);
			foreach ($keys as $key) {
				$cacher->delete($key['cid']);
			}
		}

		return Ajax::success('缓存已经清空');
	}
}
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
use cms\classes\model\CmsPage;
use wulaphp\app\App;
use wulaphp\io\Ajax;

/**
 * Class ChannelController
 * @package cms\site\controllers
 * @acl     mc:site/page
 */
class ChannelController extends IFramePageController {
	/**
	 * 移动栏目.
	 *
	 * @param string $cid  要移动的栏目ID
	 * @param string $nid  新的上级栏目ID
	 * @param string $oid  原上级栏目ID
	 * @param string $tid  相对栏目ID（调整排序时用）
	 * @param string $type 移动方式
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 * @throws
	 */
	public function move($cid, $nid, $oid, $tid, $type = '') {
		$db  = App::db();
		$nid = intval($nid);
		$oid = intval($oid);
		$cid = intval($cid);
		$tid = intval($tid);
		$db->start();
		$table         = new CmsPage($db);
		$revData       = $table->loadRev($cid, 1);
		$needUpdateRev = false;
		try {
			if ($nid != $oid) {
				//新的上级栏目
				$nch = ['path' => '/'];
				if ($nid) {
					$nch = $db->queryOne('SELECT path FROM {cms_page} WHERE id = ' . $nid);
				}
				//原上级栏目
				$och = ['path' => '/'];
				if ($oid) {
					$och = $db->queryOne('SELECT path FROM {cms_page} WHERE id = ' . $oid);
				}
				$cch = $db->queryOne('SELECT path FROM {cms_page} WHERE id = ' . $cid);
				if (!$cch || $cch['path'] == '/') {
					throw_exception('你要移动的栏目在西天吗?');
				}
				//当前栏目的channel=$nid
				$ch['channel'] = $nid;
				if (!$db->update('{cms_page_field}')->set($ch)->where(['page_id' => $cid])->exec()) {
					throw_exception('无法更新栏目');
				}
				//更新他的版本数据
				$revData['channel'] = $nid;
				$needUpdateRev      = true;
				//更新path
				$where['path %'] = $cch['path'] . '%';
				$len             = strlen($och['path']) + 1;
				$np              = $nch['path'];
				$data['path']    = imv("CONCAT('$np',SUBSTR(path,$len))")->noquote();
				$rst             = $db->update('{cms_page}')->set($data)->where($where)->exec();
				if (!$rst) {
					throw_exception('更新路径出错[1]');
				}
				$rst = $db->update('{cms_page_field}')->set($data)->where($where)->exec();
				if (!$rst) {
					throw_exception('更新路径出错[2]');
				}
			}
			if ($tid) {
				//排序
				$tch = $db->queryOne('SELECT display_sort FROM {cms_channel} WHERE page_id = ' . $tid);
				$cch = $db->queryOne('SELECT display_sort FROM {cms_channel} WHERE page_id = ' . $cid);
				if ($tch) {
					if ($type == 'inner') {
						$tsort = -1;
						$csort = 0;
					} else {
						//互换排序
						$csort = $tch['display_sort'];
						if ($nid > 0) {
							$tsort = $cch['display_sort'];
						} else {
							$tsort = -1;
						}
					}
					$db->cudx('UPDATE {cms_channel} SET display_sort = %d WHERE page_id = %d', $csort, $cid);
					$revData['display_sort'] = $csort;
					$needUpdateRev           = true;
					if ($tsort >= 0) {
						$db->cudx('UPDATE {cms_channel} SET display_sort = %d WHERE page_id = %d', $tsort, $tid);
						$trevData = $table->loadRev($tid, 1);
						if ($trevData) {
							$trevData['display_sort'] = $tsort;
							$table->newRev($tid, $trevData, $db);
						}
					}
				}
			}
			if ($needUpdateRev && !$table->newRev($cid, $revData, $db)) {
				throw_exception('无法更新栏目版本数据');
			}
			$db->commit();

			return Ajax::success();
		} catch (\Exception $e) {
			$db->rollback();

			return Ajax::error($e->getMessage());
		}

	}
}
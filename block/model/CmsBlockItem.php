<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\block\model;

use cms\classes\model\CmsPage;
use wulaphp\form\FormTable;

class CmsBlockItem extends FormTable {

	public function updateItem($item, $uid = 0) {
		if ($item['page_id']) {
			$page = $this->getPageInfo($item['page_id']);
			if ($page) {
				if (empty($item['title'])) {
					$item['title'] = $page['title'];
				}
				if (empty($item['title2'])) {
					$item['title2'] = $page['title2'];
				}
				if (empty($item['url'])) {
					$item['url'] = $page['url'];
				}
				if (empty($item['image'])) {
					$item['image'] = $page['image'];
				}
				if (empty($item['image1'])) {
					$item['image1'] = $page['image1'];
				}
				if (empty($item['image2'])) {
					$item['image2'] = $page['image2'];
				}
			}
		}
		$item['update_time'] = time();
		$item['update_uid']  = intval($uid);

		return $this->update($item, $item['id']);
	}

	public function newItem($item, $uid = 0) {
		if ($item['page_id']) {
			$page = $this->getPageInfo($item['page_id']);
			if ($page) {
				if (empty($item['title'])) {
					$item['title'] = $page['title'];
				}
				if (empty($item['title2'])) {
					$item['title2'] = $page['title2'];
				}
				if (empty($item['url'])) {
					$item['url'] = $page['url'];
				}
				if (empty($item['image'])) {
					$item['image'] = $page['image'];
				}
				if (empty($item['image1'])) {
					$item['image1'] = $page['image1'];
				}
				if (empty($item['image2'])) {
					$item['image2'] = $page['image2'];
				}
			}
		}
		unset($item['id']);
		$item['create_time'] = $item['update_time'] = time();
		$item['create_uid']  = $item['update_uid'] = intval($uid);

		return $this->insert($item);
	}

	public function deleteItems($ids) {
		return $this->delete(['id IN' => $ids]);
	}

	private function getPageInfo($id) {
		$page = new CmsPage();
		$data = $page->loadFields($id, false);

		return $data;
	}
}
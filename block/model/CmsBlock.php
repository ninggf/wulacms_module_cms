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

use wulaphp\form\FormTable;

class CmsBlock extends FormTable {
	public function newBlock($data, $uid = 0) {
		$data['create_time'] = $data['update_time'] = time();
		$data['create_uid']  = $data['update_uid'] = intval($uid);

		return $this->insert($data);
	}

	public function updateBlock($data, $uid = 0) {
		$data['update_time'] = time();
		$data['update_uid']  = intval($uid);

		return $this->update($data, $data['id']);
	}

	/**
	 * 删除区块
	 *
	 * @param string|int $id
	 *
	 * @return bool
	 */
	public function deleteBlock($id) {
		if (empty($id)) {
			return false;
		}
		$rst = $this->delete($id);
		$rst = $rst && $this->dbconnection->cudx('DELETE FROM {cms_block_item} WHERE block_id = %d', $id);

		return $rst;
	}
}
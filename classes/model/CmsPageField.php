<?php
/**
 * DEC : cms_page_field model
 * User: wangwei
 * Time: 2018/2/2 下午5:52
 */

namespace cms\classes\model;

use wulaphp\db\Table;

class CmsPageField extends Table {

	public function add($data) {
		if (!$data) {
			return false;
		}
		$a = $this->insert($data);

		return true;
	}

	public function updatePageField($data, $cond = []) {
		return $this->update($data, $cond);
	}

	public function updateNode($np,$opath){
		$len = strlen($opath)+1;
		$data['path'] = imv("CONCAT('$np',SUBSTR(path,$len))")->noquote();
		try {
			$this->update($data, ['path LIKE' => $opath . '%']);
		}catch (\Exception $e){
			throw_exception($e->getMessage());
		}
		return true;
	}
}
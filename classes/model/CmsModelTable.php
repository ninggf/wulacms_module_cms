<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\model;

use wulaphp\app\App;
use wulaphp\db\DatabaseConnection;
use wulaphp\db\Table;

class CmsModelTable extends Table {
	/**
	 * 更新模型.
	 *
	 * @param string|int $id   ID
	 * @param array      $data 要更新的数据
	 *
	 * @return bool
	 */
	public function updateModel($id, $data) {
		return $this->update($data, $id);
	}

	/**
	 * 添加模型.
	 *
	 * @param array $data 模型数据.
	 *
	 * @return bool|int
	 */
	public function newModel($data) {
		return $this->insert($data);
	}

	/**
	 * 删除指定模型.
	 *
	 * @param array $ids
	 *
	 * @return bool
	 */
	public function deleteModel($ids) {
		if ($ids) {
			$refids = $this->get(['id IN' => $ids], 'refid')->toArray('refid');
			foreach ($refids as $refid) {
				if (!self::uninstall($refid)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * 获取模型的属性.
	 *
	 * @param string $refid
	 *
	 * @return array
	 */
	public function getFlags($refid) {
		$flags = $this->get(['refid' => $refid], 'flags')->get('flags');
		if ($flags) {
			$flags  = explode(',', $flags);
			$rflags = [];
			foreach ($flags as $f) {
				$rf            = trim($f);
				$rflags[ $rf ] = $rf;
			}

			return $rflags;
		}

		return [];
	}

	/**
	 * 安装一个模型.
	 *
	 * @param string $name
	 * @param string $refid
	 * @param string $flags
	 * @param int    $creatable
	 * @param int    $hidden
	 *
	 * @return bool|int
	 */
	public final static function install($name, $refid, $flags = '', $creatable = 1, $hidden = 0) {
		$data['name']      = $name;
		$data['refid']     = $refid;
		$data['flags']     = $flags;
		$data['creatable'] = $creatable;
		$data['hidden']    = $hidden;

		$tb = new self();

		return $tb->newModel($data);
	}

	/**
	 * 卸载模型.
	 *
	 * @param string $refid
	 *
	 * @return bool
	 */
	public static function uninstall($refid) {
		try {
			$db = App::db();

			return $db->trans(function (DatabaseConnection $dbx) use ($refid) {
				$model = $dbx->queryOne('select id from {cms_model} where refid = %s', $refid);
				if (!$model) {
					return false;
				}
				//删除模型
				$rst = $dbx->cud('DELETE FROM {cms_model} WHERE refid = %s', $refid);
				if ($rst === null) {
					return false;
				}
				//删自定义字段
				$rst = $dbx->cud('DELETE FROM {cms_model_field} WHERE model = ' . $model['id']);
				if ($rst === null) {
					return false;
				}
				//删除路由表
				$rst = $dbx->cud('DELETE CR FROM {cms_router} AS CR INNER JOIN {cms_page} AS CP ON CR.id = CP.id WHERE model = ' . $model['id']);
				if ($rst === null) {
					return false;
				}
				//删除相关表
				$rts = ['cms_page_rev', 'cms_page_flag', 'cms_page_tag', 'cms_page_field'];
				foreach ($rts as $tt) {
					$rst = $dbx->cud('DELETE CPF FROM {' . $tt . '} AS CPF LEFT JOIN {cms_page} AS CP ON CPF.page_id = CP.id WHERE CP.model =' . $model['id']);
					if ($rst === null) {
						return false;
					}
				}
				//删除
				$rst = $dbx->cud('DELETE FROM {cms_page} WHERE model = ' . $model['id']);
				if ($rst === null) {
					return false;
				}

				return true;
			});
		} catch (\Exception $e) {
			return false;
		}
	}
}
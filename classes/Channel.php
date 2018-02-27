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

use wulaphp\util\ArrayCompare;

/**
 * 菜单类.
 *
 * @package dashboard\classes
 */
class Channel {
	public  $id;
	public  $name;
	public  $level     = 1;
	public  $pos       = 0;

	public  $children     = [];


	public function __construct($id, $name = '') {
		$this->id      = $id;
		$this->name    = $name;
	}

	/**
	 * 是否有子菜单.
	 *
	 * @return bool 有返回true，反之返回false.
	 */
	public function hasSubMenu() {
		return count($this->children) > 0;
	}

	/**
	 * 获取子菜单。
	 *
	 * @param string   $id   菜单ID
	 * @param string   $name 菜单名称
	 * @param int|null $pos  菜单位置
	 *
	 * @return \cms\classes\Channel 菜单实例的引用
	 */
	public function &getMenu($id, $name = '', $pos = null) {
		if (!isset ($this->children [ $id ])) {
			$this->children[ $id ]        = new Channel($id);
			$this->children[ $id ]->level = $this->level + 1;
			if ($name) {
				$this->children[ $id ]->name = $name;
			}
		}

		return $this->children[ $id ];
	}

	/**
	 * 生成菜单数据.
	 *
	 * @param bool $group 是否启用分组(在下拉菜单时有用)
	 *
	 * @return array 菜单数据
	 *
	 * ```{.json}
	 * {
	 *     "id":"menu id",
	 *     "name":"系统",
	 *     "h5datas":"data-name=\"abc\" data-id=...",
	 *     "children":[{
	 *          "id":"menu id"
	 *          ...
	 *     },...]
	 * }
	 * ```
	 */
	public function data($group = false) {
		$data  = get_object_vars($this);
		$datas = $data['data'];
		unset($data['data'], $data['cpos']);
		foreach (array_keys($data) as $key) {
			if (empty($data[ $key ])) {
				unset($data[ $key ]);
			} else if (is_string($data[ $key ])) {
				$data[ $key ] = trim($data[ $key ]);
			}
		}
		if ($datas) {
			$h5data = [];
			foreach ($datas as $key => $v) {
				$tkey = trim($key);
				if (is_array($v)) {
					$data['data'][ $tkey ] = $v;
				} else {
					$data['data'][ $tkey ] = $h5data[ 'data-' . $tkey ] = trim($v);
				}
			}
		}
		$data['children'] = [];
		if ($this->children) {
			foreach ($this->children as $item) {
				$data['children'][] = $item->data($group);
			}
			usort($data['children'], ArrayCompare::compare('pos'));
		}
		unset($data['level']);

		return $data;
	}
}
<?php
/**
 * DEC : cms_page model
 * User: wangwei
 * Time: 2018/2/1 下午1:28
 */

namespace cms\classes\model;

use wulaphp\db\Table;
use wulaphp\util\ArrayCompare;

class CmsPage extends Table {
	private $menus = [];
	public  $level = 1;

	public function getChannelTree($path = '') {
		//根据path寻找子类
		if ($path) {
			$where['CP.path LIKE'] = $path . '%';
		}
		$where['CP.status'] = 1;
		$where['CP.model']  = 1;
		$query              = $this->alias('CP')->select('CP.id,CP.model,CP.path,CP.url,CPF.title')->join('{cms_page_field} as CPF', 'CP.id=CPF.page_id')->where($where);
		foreach ($query as $q) {

			$this->getMenu($q['path'], $q['title'], $q['path'], $q['id']);
		}

		return $this->menuData();
	}

	/**
	 * 获取导航菜单.
	 *
	 * @param string   $id   菜单ID
	 * @param string   $name 菜单名称
	 * @param int|null $pos  位置
	 *
	 * @return \cms\classes\Channel Menu实例的引用
	 */
	public function &getMenu($id, $name = '', $url = null, $pid = 0) {
		$ids = explode('/', trim($id, '/'));
		$id  = array_shift($ids);
		if (isset ($this->menus [ $id ])) {
			$menu = $this->menus [ $id ];
		} else {
			$menu                = new \cms\classes\Channel($id);
			$this->menus [ $id ] = &$menu;
		}
		if ($ids) {
			foreach ($ids as $id) {
				$menu = $menu->getMenu($id);
			}
		}
		if ($name) {
			$menu->name = $name;
		}
		if ($url != null) {
			$menu->url = $url;
		} else if (!$menu->url) {
			$menu->url = '';
		}
		$menu->id = $pid;

		return $menu;
	}

	/**
	 * 获取菜单数据.
	 *
	 * @param bool $group 是否启用分组(在下拉菜单时有用)
	 *
	 * @return array 菜单数据
	 */
	public function menuData($group = false) {
		$menus = ['menus' => []];
		/** @var \cms\classes\Channel $menu */
		foreach ($this->menus as $menu) {
			$menus['menus'][] = $menu->data($group);
		}
		usort($menus['menus'], ArrayCompare::compare('pos1'));
		//处理分组
		if ($group) {
			$gdata = [];
			foreach ($menus['menus'] as $cd) {
				$gp = $cd['group'];
				unset($cd['group']);
				$gdata[ $gp ][] = $cd;
			}
			$_tpmenus = [];
			foreach ($gdata as $gds) {
				$_tpmenus   = array_merge($_tpmenus, $gds);
				$_tpmenus[] = ['name' => 'divider'];
			}
			array_pop($_tpmenus);
			$menus['menus'] = $_tpmenus;
		}

		return $menus;
	}

	public function get_one($cond = []) {
		if (!is_array($cond)) {
			$where['id'] = $cond;
		} else {
			$where = $cond;
		}
		$res = $this->select('*')->where($where)->get(0);

		return $res;
	}

	public function add($data) {
		if (!$data) {
			return false;
		}

		return $this->insert($data);
	}

	public function get_page($page_id = 0) {
		$where['CP.id'] = $page_id;
		$res            = $this->alias('CP')->select('CP.id,CP.model,CP.path,CP.url,CPF.title,CPF.title2,CPF.keywords,CPF.description')->join('{cms_page_field} as CPF', 'CP.id=CPF.page_id')->where($where)->get();

		return $res;

	}

	public function updatePage($data, $cond = []) {
		return $this->update($data, $cond);
	}

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

	public function delNode($id) {
		$path = $this->select('path')->where(['id' => $id])->get('path');

		return $this->update(['status' => 2], ['path LIKE' => $path . '%', 'status' => 1]);
	}
}
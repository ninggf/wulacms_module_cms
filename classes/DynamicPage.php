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

use cms\classes\model\CmsPage;

/**
 * 动态页（路由）
 */
class DynamicPage extends ModelDoc {
	public function id() {
		return 'dynamic';
	}

	public function name() {
		return '动态模板页';
	}

	public function getForm($id, &$data) {
		$form = new DynamicPageForm(true);
		if ($id) {
			//还原URL
			$data['url'] = substr($data['url'], strlen($data['cchannel']['path']) - 1);
		}

		return $form;
	}

	public function getTpl($id, &$data) {
		return 'page/dynamic';
	}

	/**
	 * @param array      $data
	 * @param string|int $uid
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function save(&$data, $uid) {
		$id = $data['id'];
		unset($data['id']);
		$page        = $this->commonData($data);
		$page['url'] = ltrim($page['path'] . $data['url'], '/');
		$url         = self::parseRoute($page['url'], $cnt);
		if (!$url) {
			$this->error = '页面URL路由规则不正确';

			return false;
		}
		$cnt                  = $cnt < 10 ? '0' . $cnt : $cnt;
		$page['content_file'] = $cnt . '@' . $url;
		//cms_page
		$table      = new CmsPage();
		$table->uid = $uid;
		if (!$id) {
			$page['create_time'] = time();
			$page['create_uid']  = intval($uid);
			//创建新页面
			$id = $table->newPage($page, $this->error);
			if (!$id) {
				return false;
			}
		} else {
			$page['update_time'] = time();
			$page['update_uid']  = intval($uid);
			$rst                 = $table->updatePage($id, $page, $this->error);
			if (!$rst) {
				return false;
			}
		}
		//最后一定要给id赋值
		$data['id'] = $id;

		return true;
	}

	public function loadData($page, $limit, $chid, $mid, $status, $sort) {
		$table                = new CmsPage();
		$where['CPF.channel'] = $chid;
		$where['CPF.model']   = $mid;
		$this->buildSearch($where);
		$q = $table->alias('CP')->select('CPF.page_id,CPF.title2,CPF.template_file,CP.url,CU.nickname AS create_uid,UU.nickname AS update_uid,PU.nickname AS publisher,CP.create_time AS create_time,CPF.update_time AS update_time,CPR.publish_time AS publish_time')->page($page, $limit);

		if ($status < 10) {//从cms_page_rev表读取
			$where['CPF.status']   = $status;//0=>草稿,1=>待审核，2=>未核准
			$where['CP.status <>'] = 2;
			$q->join('{cms_page_rev} AS CPF', 'CPF.page_id = CP.id');
			$q->sort('CPF.ver', 'd');
			$q->field('CPF.ver', 'ver');
			$q->field('CPF.status', 'revStatus');
		} else {//从cms_page_field表读取
			$where['CPF.status'] = $status - 10;//1,2
			$q->join('{cms_page_field} AS CPF', 'CPF.page_id = CP.id');
			$q->field('CP.ver', 'ver');
		}

		$q->join('{user} AS CU', 'CU.id = CP.create_uid');
		$q->join('{user} AS UU', 'UU.id = CPF.update_uid');
		$q->join('{cms_page_rev} AS CPR', 'CPR.page_id = CP.id AND CP.ver = CPR.ver');
		$q->join('{user} AS PU', 'PU.id = CPR.publisher');
		$sort = $this->alterSort();
		$q->sort($sort['name'], $sort['dir'])->where($where);
		$total = $q->total('CP.id');

		$data = $q->toArray();
		foreach ($data as $k => &$v) {
			$v['create_time'] = date('Y-m-d H:i', $v['create_time']);
			$v['update_time'] = date('Y-m-d H:i', $v['update_time']);
			if ($v['publish_time']) {
				$v['publish_time'] = date('Y-m-d H:i', $v['publish_time']);
			} else {
				$v['publish_time'] = '';
			}
		}

		return $this->formatGridData($data, $total);
	}

	/**
	 * 表格
	 * @return array
	 */
	public function gridCols() {
		$i               = 1;
		$cols            = parent::gridCols();
		$cols[0][ ++$i ] = [
			'field' => 'ver',
			'title' => '版本',
			'sort'  => true,
			'width' => 65
		];
		$cols[0][ ++$i ] = [
			'field' => 'url',
			'title' => '路由规则',
			'sort'  => true,
			'width' => 250
		];
		$cols[0][ ++$i ] = [
			'field' => 'title2',
			'title' => '页面名称',
			'width' => 200
		];
		$cols[0][ ++$i ] = [
			'field' => 'template_file',
			'title' => '模板',
			'width' => 150
		];
		$cols[0][ ++$i ] = [
			'field' => 'update_time',
			'title' => '最后更新时间',
			'sort'  => true,
			'width' => 150
		];
		$cols[0][ ++$i ] = [
			'field' => 'update_uid',
			'title' => '最后更新者',
			'sort'  => true,
			'width' => 120
		];
		$cols[0][ ++$i ] = [
			'field' => 'create_time',
			'title' => '创建时间',
			'sort'  => true,
			'width' => 150
		];
		$cols[0][ ++$i ] = [
			'field' => 'create_uid',
			'title' => '创建者',
			'sort'  => true,
			'width' => 120
		];

		return $cols;
	}

	private function alterSort() {
		$sort = rqst('sort');
		if ($sort && $sort['name']) {
			$sort['name'] = str_replace(['update_', 'create_', 'url'], [
				'CPF.update_',
				'CP.create_',
				'CPF.content_file'
			], $sort['name']);
		}

		return $sort;
	}

	/**
	 * 解析路由规则.
	 *
	 * @param string $url
	 * @param mixed  $cnt
	 *
	 * @return int|string
	 */
	public static function parseRoute($url, &$cnt = null) {
		$cnt  = 0;
		$rurl = preg_replace_callback('#\(([ds\*])\)#', function ($m) use (&$cnt) {
			$cnt++;
			if ($m[1] == 'd') {
				return '[?[\d]+?]';
			} else if ($m[1] == 's') {
				return '[?[a-z\-]+?]';
			} else {
				return '[?[a-z\-\d]+?]';
			}
		}, $url);
		if ($cnt > 0 && strpos($rurl, '(') === false && strpos($rurl, ')') === false) {

			return str_replace(['[?', '?]', '.'], ['(', '?)', '\.'], $rurl);
		}

		return 0;
	}
}
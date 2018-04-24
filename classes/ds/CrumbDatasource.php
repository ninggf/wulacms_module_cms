<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\ds;

use wulaphp\app\App;
use wulaphp\form\FormTable;
use wulaphp\mvc\model\CtsData;
use wulaphp\mvc\model\CtsDataSource;

class CrumbDatasource extends CtsDataSource {
	public function getName() {
		return '栏目导航';
	}

	protected function getData($con, $db, $pageInfo, $tplvar) {
		$path = aryget('path', $con);
		try {
			$db   = App::db();
			$down = aryget('down', $con);
			if ($down) {
				$data = $this->getSubCh($db, $path);
			} else {
				$data = $this->getCrumb($db, $path);
			}
		} catch (\Exception $e) {
			$data = [];
		}

		return new CtsData($data, count($data));
	}

	/**
	 * @param \wulaphp\db\DatabaseConnection $db
	 * @param string                         $path
	 *
	 * @return array
	 */
	private function getSubCh($db, $path) {
		if (empty($path) || $path == '/' || is_numeric($path)) {
			$path = intval(trim($path, '/'));
			$sql  = <<<SQL
SELECT CPF.*,CP.url,CP.id
	FROM {cms_page_field} AS CPF 
		LEFT JOIN {cms_page} AS CP ON CPF.page_id = CP.id 
		LEFT JOIN {cms_channel} AS CH ON CPF.page_id = CH.page_id
	WHERE CPF.channel = $path AND CPF.model = 1 AND CPF.status = 1 ORDER BY display_sort ASC
SQL;
			$chs  = $crumb = $db->query($sql);
		} else {
			$sql = <<<SQL
SELECT CPF.*,CP.url,CP.id
	FROM {cms_page_field} AS CPF 
		LEFT JOIN {cms_page} AS CP ON CPF.page_id = CP.id 
		LEFT JOIN {cms_channel} AS CH ON CPF.page_id = CH.page_id
		LEFT JOIN {cms_page} AS CHP ON (CPF.channel = CHP.id)
	WHERE CHP.path = %s AND CHP.model = 1 AND CPF.model = 1 AND CPF.status = 1 ORDER BY display_sort ASC
SQL;
			$chs = $crumb = $db->query($sql, $path);
		}

		if ($chs) {
			return $chs;
		}

		return [];
	}

	/**
	 * @param \wulaphp\db\DatabaseConnection $db
	 * @param string                         $path
	 *
	 * @return array
	 */
	private function getCrumb($db, $path) {
		if (empty($path) || $path == '/') {
			return [];
		}
		$paths = explode('/', trim($path, '/'));
		$sql   = <<<SQL
SELECT CPF.*,CP.url,CP.id
	FROM {cms_page_field} AS CPF 
		LEFT JOIN {cms_page} AS CP ON CPF.page_id = CP.id 
	WHERE CPF.path = %s AND CPF.model = 1 LIMIT 0,1
SQL;
		$data  = [];
		while ($paths) {
			$pt    = '/' . implode('/', $paths) . '/';
			$crumb = $db->queryOne($sql, $pt);
			if (!$crumb) {
				break;
			}
			$data[] = $crumb;
			array_pop($paths);
		}
		if (count($data) > 1) {
			$data = array_reverse($data);
		}

		return $data;
	}

	public function getCondForm() {
		return new CrumbDatasourceForm(true);
	}

	public function getCols() {
		return [
			'id'     => 'ID',
			'title2' => '栏目名称',
			'title'  => '栏目标题'
		];
	}
}

class CrumbDatasourceForm extends FormTable {
	public $table = null;
	/**
	 * 栏目路径.
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 1,col-xs-12
	 */
	public $path;
	/**
	 * 查找子级
	 * @var \backend\form\CheckboxField
	 * @type bool
	 * @layout 1,col-xs-12
	 */
	public $down;
}
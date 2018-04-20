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

use wulaphp\form\FormTable;
use wulaphp\mvc\model\CtsData;
use wulaphp\mvc\model\CtsDataSource;

class PageDatasource extends CtsDataSource {

	public function getName() {
		return '通用页面';
	}

	protected function getData($con, $db, $pageInfo, $tplvar) {
		$id = aryget('id',$con);

		return new CtsData([
			['id' => 1, 'name' => $pageInfo->page,'title'=>$id],
			['id' => 2, 'name' => $pageInfo->page,'title'=>$id]
		], 19);
	}

	public function getVarName() {
		return 'page';
	}

	public function getCondForm() {
		return new PageDatasourceForm(true);
	}

	public function getCols() {
		return ['id' => 'ID', 'name' => '名称', 'title' => '标题'];
	}
}

class PageDatasourceForm extends FormTable {
	public $table = null;
	/**
	 * ID
	 * @var \backend\form\TextField
	 * @type int
	 * @layout 1,col-xs-12
	 */
	public $id = 0;
}
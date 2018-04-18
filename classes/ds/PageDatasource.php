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

use wulaphp\mvc\model\CtsData;
use wulaphp\mvc\model\CtsDataSource;

class PageDatasource extends CtsDataSource {

	public function getName() {
		return '通用页面';
	}

	protected function getData($con, $db, $pageInfo, $tplvar) {
		return new CtsData([
			['id' => 1, 'name' => 2],
			['id' => 2, 'name' => 2]
		], 19);
	}

	public function getVarName() {
		return 'pages';
	}
}
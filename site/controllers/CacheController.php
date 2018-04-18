<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\site\controllers;

use backend\classes\IFramePageController;
use wulaphp\io\Ajax;

/**
 * Class CacheController
 * @package cms\site\controllers
 * @acl     hc:site/page
 */
class CacheController extends IFramePageController {
	/**
	 * 清空页面缓存
	 *
	 * @param string $id
	 * @param string $type 为1时清空栏目下所有页面的缓存.
	 *
	 * @return \wulaphp\mvc\view\JsonView
	 */
	public function clear($id, $type = '') {
		//TODO 清空缓存
		return Ajax::success('缓存已经清空');
	}
}
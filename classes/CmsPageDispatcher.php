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

use wulaphp\router\IURLDispatcher;

/**
 * 内容管理系统的页面路由器.
 *
 * @package cms\classes
 */
class CmsPageDispatcher implements IURLDispatcher {
	/**
	 * @param string                        $url
	 * @param \wulaphp\router\Router        $router
	 * @param \wulaphp\router\UrlParsedInfo $parsedInfo
	 *
	 * @return \wulaphp\mvc\view\View
	 */
	public function dispatch($url, $router, $parsedInfo) {
		$view = $this->forStaticPage($url, $router, $parsedInfo);

		return $view ? $view : $this->forDynamicPage($url, $router, $parsedInfo);
	}

	/**
	 * @param string                        $url
	 * @param \wulaphp\router\Router        $router
	 * @param \wulaphp\router\UrlParsedInfo $parsedInfo
	 *
	 * @return \wulaphp\mvc\view\View
	 */
	private function forStaticPage($url, $router, $parsedInfo) {
		//TODO: 完成它
		return null;
	}

	/**
	 * @param string                        $url
	 * @param \wulaphp\router\Router        $router
	 * @param \wulaphp\router\UrlParsedInfo $parsedInfo
	 *
	 * @return \wulaphp\mvc\view\View
	 */
	private function forDynamicPage($url, $router, $parsedInfo) {
		//TODO: 完成它
		return null;
	}
}
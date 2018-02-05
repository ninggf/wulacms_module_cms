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
use cms\classes\model\CmsPageField;
use cms\classes\model\CmsRouter;
use wulaphp\cache\RtCache;
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
		$md5_url  = md5($url);
		$url_key  = 'rt@' . $md5_url;
		$url_path = RtCache::get($url_key);
		if ($url_path) {
			return template($url_path);
		} else {
			//Find template_file
			$cms_router_model   = new CmsRouter();
			$where['CR.route']  = $md5_url;//md5 url
			$where['CP.status'] = 1;//The status is publish
			$page_info          = $cms_router_model->alias('CR')->select('CPF.*,CCM.template_file as ccm_template_file')->join('{cms_page_field} AS CPF', 'CR.id=CPF.page_id')->join('{cms_page} AS CP', 'CR.id=CP.id')->join('{cms_channel_model} AS CCM', 'CR.id=CCM.page_id')->where($where)->get(0);
			if ($page_info) {
				//The CPF table has template_file
				if ($page_info['template_file']) {
					RtCache::add($url_key, $page_info['template_file']);

					return template($page_info['template_file']);
				} // The CCM table has template_file
				else if ($page_info['ccm_template_file']) {
					RtCache::add($url_key, $page_info['ccm_template_file']);

					return template($page_info['ccm_template_file']);
				} else {
					return null;
				}
			}
		}

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
		$cms_page_model = new CmsPage();
		$pages          = $cms_page_model->select('url,id')->where(['model' => 0, 'status' => 1]);
		$routers        = [];
		foreach ($pages as $page) {
			$reg                    = $page['url'];
			$i                      = 0;
			$reg                    = preg_replace_callback('#\(([^)]+)\)#', function ($ms) use (&$i) {
				$i++;
				$p = $ms[1];
				if ($p == '*') {
					return "(?P<arg{$i}>.+?)";
				} else {
					return "(?P<arg{$i}>$p)";
				}
			}, $reg);
			$routers[ $page['id'] ] = '#^' . $reg . '$#i';
		}
		$page = null;
		$max  = 0;
		$url  = urldecode($url);
		if (!preg_match('#.+\.(s?html?|xml|jsp|json)$#i', $url)) {
			$url = $url . '/index.html';
		}
		foreach ($routers as $pid => $reg) {
			if (preg_match($reg, $url, $ms)) {
				$mc = count($ms);
				if ($mc > $max) {
					$max  = $mc;
					$page = ['id' => $pid, 'args' => $ms];
				}
			}
		}
		if ($page) {
			//Find the template_file
			$cms_page_field_model = new CmsPageField();
			$template_file        = $cms_page_field_model->select('template_file')->where(['page_id' => $page['id']])->get('template_file');
			if ($template_file) {
				return template($template_file, $page);
			}

		}

		return null;
	}

}
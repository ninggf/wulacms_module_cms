<?php

namespace cms\classes;

use cms\classes\model\CmsPage;
use wulaphp\app\App;
use wulaphp\io\Request;
use wulaphp\mvc\view\View;
use wulaphp\router\IURLDispatcher;
use wulaphp\router\Router;
use wulaphp\router\RouteTableDispatcher;
use wulaphp\router\UrlParsedInfo;

/**
 * Class CmsDispatcher
 * @package cms\classes
 */
class CmsDispatcher implements IURLDispatcher {
	private $domain;
	private $next = true;//是否解析动态页

	public function __construct() {
		//返回域名配置的主题
		bind('get_theme', function ($th) {
			if ($th == 'default') {
				$th = aryget('theme', self::getCurrentDomain(), 'default');
			}

			return $th;
		}, 0);
	}

	/**
	 * 分发URL.
	 * 一旦有一个分发器返回View实例，则立即返回，停止分发其它的.
	 *
	 * @param string        $url        URL.
	 * @param Router        $router     路由器.
	 * @param UrlParsedInfo $parsedInfo URL解析信息.
	 *
	 * @return View View 实例.
	 */
	public function dispatch($url, $router, $parsedInfo) {
		$route = RouteTableDispatcher::parseURL($parsedInfo);
		//解析不了
		if (!$route) {
			return null;
		}
		//检测扩展名,目前只解析以下扩展名
		if (!preg_match('#^(html?|xml|png|json|txt|gif)$#', $parsedInfo->ext)) {
			return null;
		}
		//域名检测
		$this->domain = $this->checkDomain();
		if (!$this->domain) {
			return null;
		}
		//网站离线
		if ($this->domain['offline']) {
			return template('offline.tpl');
		}
		//首页
		if ($route == 'index.html') {
			return $this->renderHomePage($parsedInfo);
		} else {
			//静态页
			$view = $this->renderStaticPage($route, $parsedInfo);
			if (!$view && $this->next) {
				//自定义路由页
				$view = $this->renderTplPage($route, $parsedInfo);
			}

			return $view;
		}
	}

	/**
	 * 获取当前域名数据.
	 *
	 * @return array
	 */
	public static function getCurrentDomain() {
		static $domain = null;
		if ($domain === null) {
			try {
				$db     = App::db();
				$domain = $db->queryOne('SELECT * FROM {cms_domain} WHERE domain = %s LIMIT 0,1', VISITING_DOMAIN);
				if (!$domain) {
					$domain = $db->queryOne('SELECT * FROM {cms_domain} WHERE is_default = 1 LIMIT 0,1');
				}

			} catch (\Exception $e) {
				$domain = [];
			}
		}

		return $domain;
	}

	/**
	 * 静态页面.
	 *
	 * @param string        $route
	 * @param UrlParsedInfo $parsedInfo
	 *
	 * @return null
	 */
	private function renderStaticPage($route, $parsedInfo) {
		$key = md5($route);
		try {
			$page = new CmsPage();
			$data = $page->load($key, $parsedInfo);
			if ($data) {
				$tpl = $data['template_file'];
				if (!$tpl) {
					$cp  = $parsedInfo->page;
					$tpl = $data['default_page_tpl'];
					if ($cp > 1 && $data['list_page_tpl']) {
						$tpl = $data['list_page_tpl'];
					}
				}
				if (!$tpl) {
					$this->next = false;

					return null;
				}
				@define('CMS_REAL_PAGE', $data);
				if (empty($data['expire'])) {
					$data['expire'] = $this->domain['expire'];
				}
				if ($data['expire']) {
					@define('CACHE_EXPIRE', $data['expire']);
				}
				$data['urlInfo']    = $parsedInfo;
				$data['routerArgs'] = [];
				$parsedInfo->setPageData($data);
				$headers = ['Content-Type' => $parsedInfo->contentType];

				return template($tpl, $data, $headers);
			}
		} catch (\Exception $e) {

		}

		return null;
	}

	/**
	 * 模板页
	 *
	 * @param string        $route
	 * @param UrlParsedInfo $parsedInfo
	 *
	 * @return null
	 */
	private function renderTplPage($route, $parsedInfo) {
		try {
			$db  = App::db();
			$sql = 'SELECT id,url,content_file AS content FROM {cms_page} AS CP INNER JOIN {cms_page_field} AS CPF ON CP.id = CPF.page_id WHERE CP.model = 2 AND CP.status = 1 ORDER BY content_file DESC';
			$rts = $db->fetch($sql);
			if ($rts) {
				$pid  = 0;
				$args = [];
				while ($row = $rts->fetch(\PDO::FETCH_ASSOC)) {
					$content = $row['content'];
					if ($content) {
						$content = substr($content, 3);
						$content = '#^' . $content . '$#';
						if (preg_match($content, $route, $ms)) {
							$pid = $row['id'];
							$cnt = count($ms);
							for ($i = 1; $i < $cnt; $i++) {
								$args[ 'arg' . $i ] = $ms[ $i ];
							}
							break;
						}
					}
				}
				$rts->closeCursor();
				if ($pid) {
					$key  = md5($row['url']);
					$page = new CmsPage();
					$data = $page->load($key, $parsedInfo);
					if ($data) {
						$tpl = $data['template_file'] ? $data['template_file'] : ($data['default_tpl'] ? $data['default_tpl'] : '');
						if (!$tpl) {
							return null;
						}
						@define('CMS_REAL_PAGE', $data);
						if (empty($data['expire'])) {
							$data['expire'] = $this->domain['expire'];
						}
						if ($data['expire']) {
							@define('CACHE_EXPIRE', $data['expire']);
						}
						$data['urlInfo']    = $parsedInfo;
						$data['routerArgs'] = $args;
						//正在查看的页面数据
						$parsedInfo->setPageData($data);
						$headers = ['Content-Type' => $parsedInfo->contentType];

						return template($tpl, $data, $headers);
					}
				}
			}
		} catch (\Exception $e) {

		}

		return null;
	}

	/**
	 * @param UrlParsedInfo $parsedInfo
	 *
	 * @return null|\wulaphp\mvc\view\ThemeView
	 */
	private function renderHomePage($parsedInfo) {
		$data['id']          = 0;
		$data['title']       = $this->domain['title'];
		$data['keywords']    = $this->domain['keywords'];
		$data['description'] = $this->domain['description'];
		$data['urlInfo']     = $parsedInfo;
		$data                = apply_filter('cms\onRenderHome', $data);
		if ($data) {
			if (isset ($data ['template_file'])) {
				$tpl = $data ['template_file'];
			} else {
				$tpl = $this->domain['tpl'];
			}
			@define('CMS_REAL_PAGE', $data);
			if ($this->domain['expire']) {
				//开启缓存指令
				@define('CACHE_EXPIRE', $this->domain['expire']);
			}
			$parsedInfo->setPageData($data);

			return template($tpl, $data);
		}

		return null;
	}

	/**
	 * 检测域名.
	 *
	 * @return array|bool
	 */
	private function checkDomain() {
		$domain = self::getCurrentDomain();

		if ($domain) {
			//强制https访问.
			if ($domain['is_https'] && !Request::isHttps()) {
				return false;
			}

			return $domain;
		}

		return false;
	}
}
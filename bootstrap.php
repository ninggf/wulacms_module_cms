<?php

namespace cms;

use backend\classes\DashboardUI;
use cms\classes\CmsDispatcher;
use cms\classes\CmsPageDispatcher;
use cms\classes\form\CmsDomainForm;
use wula\cms\CmfModule;
use wulaphp\app\App;
use wulaphp\auth\AclResourceManager;
use wulaphp\router\Router;

/**
 * Class CmsModule
 * @package cms
 * @group   cms
 */
class CmsModule extends CmfModule {
	public function getName() {
		return 'CMS';
	}

	public function getDescription() {
		return '内容管理系统';
	}

	public function getHomePageURL() {
		return 'https://www.wulacms.com/modules/cms';
	}

	public function getVersionList() {
		$v['1.0.0'] = '初始版本';

		return $v;
	}

	public function getAuthor() {
		return 'Leo Ning';
	}

	/**
	 * @param \wulaphp\router\Router $router
	 *
	 * @bind router\registerDispatcher
	 */
	public static function regRouter(Router $router) {
		$router->register(new CmsPageDispatcher(), 50);
	}

	/**
	 * 注册分发器
	 *
	 * @param \wulaphp\router\Router $router
	 *
	 * @bind router\registerDispatcher
	 */
	public static function regDispatcher($router) {
		$router->register(new CmsDispatcher());
	}

	/**
	 * 初始化菜单.
	 *
	 * @param \backend\classes\DashboardUI $ui
	 *
	 * @bind dashboard\initUI
	 */
	public static function initUI(DashboardUI $ui) {
		$passport = whoami('admin');
		if ($passport->cando('m:cms')) {
			$site       = $ui->getMenu('site', '网站');
			$site->icon = '&#xe617;';
			if ($passport->cando('m:cms/page')) {
				$page              = $site->getMenu('domain', '域名', 1);
				$page->icon        = '&#xe64c;';
				$page->iconCls     = 'layui-icon';
				$page->data['url'] = App::url('cms/page/domain');
			}
			if ($passport->cando('m:cms/page')) {
				$page              = $site->getMenu('channel', '栏目', 2);
				$page->icon        = '&#xe62a;';
				$page->iconCls     = 'layui-icon';
				$page->data['url'] = App::url('cms/page/channel');
			}
			if ($passport->cando('m:site/page')) {
				$page              = $site->getMenu('page', '页面', 3);
				$page->icon        = '&#xe7a0;';
				$page->iconCls     = 'layui-icon';
				$page->data['url'] = App::url('cms/page');
			}
			if ($passport->cando('m:site/block')) {
				$page       = $site->getMenu('block', '列表', 900);
				$page->icon = '&#xe61a;';
			}
			if ($passport->cando('m:site/chunk')) {
				$page          = $site->getMenu('chunk', '部件', 901);
				$page->icon    = '&#xe634;';
				$page->iconCls = 'layui-icon';
			}
			if ($passport->cando('m:site/model')) {
				$site            = $ui->getMenu('site');
				$page            = $site->getMenu('model', '模型', 999);
				$page->icon      = '&#xe705;';
				$page->iconCls   = 'layui-icon';
				$page->iconStyle = 'color:orange';
			}
			if ($passport->is('开发人员')) {
				$page            = $site->getMenu('tpl', '模板', 2001);
				$page->icon      = '&#xe632;';
				$page->iconCls   = 'layui-icon';
				$page->iconStyle = "color:orange";
			}
		}
	}

	/**
	 * 注册权限.
	 *
	 * @param AclResourceManager $manager
	 *
	 * @bind rbac\initAdminManager
	 */
	public static function initAcl(AclResourceManager $manager) {
		$manager->getResource('site', 'CMS', 'm');

		$acl = $manager->getResource('site/page', '网页管理', 'm');
		$acl->addOperate('edit', '编辑');
		$acl->addOperate('del', '删除');
		$acl->addOperate('mc', '管理栏目');

		$acl = $manager->getResource('site/block', '列表管理', 'm');
		$acl->addOperate('edit', '编辑');
		$acl->addOperate('del', '删除');

		$acl = $manager->getResource('site/chunk', '部件管理', 'm');
		$acl->addOperate('edit', '编辑');
		$acl->addOperate('del', '删除');

		$acl = $manager->getResource('site/model', '模型管理', 'm');
		$acl->addOperate('edit', '编辑');
		$acl->addOperate('del', '删除');
	}

	/*
	 * get the theme  from  the domain
	 */
	public function get_theme() {
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] != 'off') {
			$schema = 'https://';
		} else {
			$schema = 'http://';
		}
		$domain    = $schema . $_SERVER ['HTTP_HOST'];
		$cms_table = new CmsDomainForm();
		$theme     = $cms_table->select('theme')->where(['domain' => $domain])->get('theme');

		return $theme;
	}

	/*
	 * provide hook to template
	*/
	protected function bind() {
		bind('get_theme', [$this, 'get_theme']);
	}
}

App::register(new CmsModule());
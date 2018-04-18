<?php

namespace cms;

use backend\classes\DashboardUI;
use cms\classes\ArticlePage;
use cms\classes\Catagory;
use cms\classes\CmsDispatcher;
use cms\classes\ds\PageDatasource;
use cms\classes\DynamicPage;
use cms\classes\form\CmsDomainForm;
use cms\classes\StaticPage;
use wula\cms\CmfModule;
use wulaphp\app\App;
use wulaphp\auth\AclResourceManager;

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
	 * 注册分发器
	 *
	 * @param \wulaphp\router\Router $router
	 *
	 * @bind router\registerDispatcher
	 */
	public static function regDispatcher($router) {
		$router->register(new CmsDispatcher(), 10000000);
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
		if ($passport->cando('m:sitem')) {
			$site1       = $ui->getMenu('site', '我的网站', 1);
			$site1->icon = '&#xe617;';
			$site        = $site1->getMenu('cms', '我的网站', 1);
			$site->icon  = '&#xe617;';
			if ($passport->cando('m:site/page')) {
				$page              = $site->getMenu('pages', '内容管理', 1);
				$page->icon        = '&#xe637;';
				$page->data['url'] = App::url('cms/site');

				//				if ($passport->cando('m:site/block')) {
				//					$block       = $site->getMenu('block', '页面区块', 900);
				//					$block->icon = '&#xe61a;';
				//				}

				if ($passport->cando('m:site/model')) {
					$model              = $site->getMenu('model', '内容模型', 999);
					$model->icon        = '&#xe705;';
					$model->iconCls     = 'layui-icon';
					$model->iconStyle   = 'color:orange';
					$model->data['url'] = App::url('cms/model');
				}

				if ($passport->cando('dm:site/page')) {
					$domain              = $site->getMenu('domain', '网站域名', 1000);
					$domain->icon        = '&#xe64c;';
					$domain->iconCls     = 'layui-icon';
					$domain->data['url'] = App::url('cms/domain');
				}
			}

			if ($passport->is('开发人员')) {
				$tpl              = $site1->getMenu('tpl', '模板调用', 2001);
				$tpl->icon        = '&#xe632;';
				$tpl->iconCls     = 'layui-icon';
				$tpl->iconStyle   = "color:orange";
				$tpl->data['url'] = App::url('cms/cts');
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

		$acl = $manager->getResource('site/page', '我的网站', 'm');
		$acl->addOperate('edit', '编辑页面');
		$acl->addOperate('del', '删除删除');
		$acl->addOperate('mc', '管理栏目');
		$acl->addOperate('dm', '域名管理');
		$acl->addOperate('ap', '审核页面');
		$acl->addOperate('pb', '发布页面');
		$acl->addOperate('hc', '清空缓存');

		$acl = $manager->getResource('site/block', '页面区块', 'm');
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

	/**
	 * 注册内容模型
	 *
	 * @param array $models
	 *
	 * @filter cms\initModel
	 * @return array
	 */
	public static function regModels($models) {
		$models['article']  = new ArticlePage();//文章页
		$models['catagory'] = new Catagory();//栏目
		$models['dynamic']  = new DynamicPage();//动态模板页
		$models['static']   = new StaticPage();//静态模板页

		return $models;
	}

	/**
	 * @param array $ms
	 *
	 * @filter wula\jqadmin\reg_module
	 * @return array
	 */
	public static function regLayuims($ms) {
		$ms['cms.main'] = '{/}' . App::res('cms/assets/js/main');

		return $ms;
	}

	/**
	 * 注册cts数据源。
	 *
	 * @param array $ds
	 *
	 * @filter tpl\regCtsDatasource
	 * @return array
	 */
	public static function regDss($ds) {
		$ds['page'] = new PageDatasource();

		return $ds;
	}

	/*
	 * provide hook to template
	*/
	protected function bind() {
		bind('get_theme', [$this, 'get_theme']);
		bind('cms\onCatagoryPagePublished', '&\cms\classes\Catagory');
	}
}

App::register(new CmsModule());
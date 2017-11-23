<?php

namespace cms;

use dashboard\classes\DashboardUI;
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
	 * 初始化菜单.
	 *
	 * @param \dashboard\classes\DashboardUI $ui
	 *
	 * @bind dashboard\initUI
	 */
	public static function initUI(DashboardUI $ui) {
		$passport = whoami('admin');
		if ($passport->cando('m:site')) {
			$site = $ui->getMenu('site', '网站');
			if ($passport->cando('m:site/page')) {
				$page       = $site->getMenu('page', '网页', 1);
				$page->icon = 'fa fa-copy';
				$page->url  = App::hash('~page');
			}
			if ($passport->cando('m:site/block')) {
				$page       = $site->getMenu('block', '列表', 900);
				$page->icon = 'fa fa-list-ul';
			}
			if ($passport->cando('m:site/chunk')) {
				$page       = $site->getMenu('chunk', '部件', 901);
				$page->icon = 'fa fa-columns';
			}
			if ($passport->cando('m:site/model')) {
				$site            = $ui->getMenu('site');
				$page            = $site->getMenu('model', '模型', 999);
				$page->icon      = 'fa fa-suitcase';
				$page->iconStyle = 'color:orange';
			}
			if ($passport->is('开发人员')) {
				$page            = $site->getMenu('tpl', '模板', 2001);
				$page->icon      = 'fa fa-html5';
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
}

App::register(new CmsModule());
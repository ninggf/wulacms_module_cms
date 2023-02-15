<?php

namespace cms;

use backend\classes\DashboardUI;
use cms\classes\ArticlePage;
use cms\classes\Catagory;
use cms\classes\cfg\CmsSetting;
use cms\classes\CmsDispatcher;
use cms\classes\ds\BlockDatasource;
use cms\classes\ds\CrumbDatasource;
use cms\classes\ds\PageDatasource;
use cms\classes\DynamicPage;
use cms\classes\StaticPage;
use wula\cms\CmfModule;
use wulaphp\app\App;
use wulaphp\auth\AclResourceManager;
use wulaphp\cache\Cache;

/**
 * Class CmsModule
 * @package cms
 * @group   cms
 * @subEnabled
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
        $v['1.1.0'] = '添加页面区块功能';
        $v['1.2.0'] = '添加标签内链功能';
        $v['1.2.1'] = '为表cms_block添加pn字段';
        $v['1.3.0'] = '添加表:cms_cache';
        $v['2.0.2'] = '支持绑定域名';

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
        if ($passport->cando('m:site')) {
            $site1       = $ui->getMenu('site', '我的网站', 1);
            $site1->icon = '&#xe617;';
            $site        = $site1->getMenu('cms', '我的网站', 1);
            $site->icon  = '&#xe617;';
            if ($passport->cando('m:site/page')) {
                $page              = $site->getMenu('pages', '内容管理', 1);
                $page->icon        = '&#xe637;';
                $page->data['url'] = App::url('cms/site');

                if ($passport->cando('m:site/block')) {
                    $block              = $site->getMenu('block', '页面区块', 900);
                    $block->icon        = '&#xe61a;';
                    $block->data['url'] = App::url('cms/block');
                }
                if ($passport->cando('m:site/tag')) {
                    $block              = $site->getMenu('tag', '内链标签', 950);
                    $block->icon        = '&#xe64c;';
                    $block->iconCls     = 'layui-icon';
                    $block->iconStyle   = 'color:#4cc0c1;';
                    $block->data['url'] = App::url('cms/tag');
                }
                if ($passport->cando('m:site/model')) {
                    $model              = $site->getMenu('model', '内容模型', 999);
                    $model->icon        = '&#xe705;';
                    $model->iconCls     = 'layui-icon';
                    $model->iconStyle   = 'color:orange';
                    $model->data['url'] = App::url('cms/model');
                }

                if ($passport->cando('dm:site/page')) {
                    $domain              = $site->getMenu('domain', '网站管理', 1000);
                    $domain->icon        = '&#xe617;';
                    $domain->iconStyle   = 'color:#dff0d8;';
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

        $acl = $manager->getResource('site/tag', '内链标签', 'm');
        $acl->addOperate('edit', '编辑');
        $acl->addOperate('del', '删除');
        $acl->addOperate('dict', '生成词典');

        $acl = $manager->getResource('site/model', '模型管理', 'm');
        $acl->addOperate('edit', '编辑');
        $acl->addOperate('del', '删除');
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
        $ms['cms.main'] = '{/}' . App::res('cms/main');

        return $ms;
    }

    /**
     * @param $settings
     *
     * @return mixed
     * @filter backend/settings
     */
    public static function setting($settings) {
        $settings['cms'] = new CmsSetting();

        return $settings;
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
        $ds['page']  = new PageDatasource();
        $ds['crumb'] = new CrumbDatasource();
        $ds['block'] = new BlockDatasource();

        return $ds;
    }

    /**
     * @param string $cid
     *
     * @bind on_page_cached
     */
    public static function onPageCached($cid) {
        CmsDispatcher::recordCacheInfo($cid);
    }

    /**
     * @param array $tasks
     *
     * @bind system\registerTask
     * @return array
     */
    public static function regTask($tasks) {
        $tasks['cms\classes\task\ClearCacheTask'] = '清空缓存';

        return $tasks;
    }

    /**
     * @bind clear_idxc_cache
     * @throws
     */
    public static function clearIndexCache() {
        if (APP_MODE == 'pro') {
            $cacher           = Cache::getCache();
            $where['page_id'] = 0;
            $db               = App::db();
            $keys             = $db->select('cid')->from('{cms_cache}')->where($where);
            foreach ($keys as $key) {
                $cacher->delete($key['cid']);
            }
        }
    }

    /**
     * provide hook to template
     */
    protected function bind(): ?array {
        bind('cms\onCatagoryPagePublished', '&\cms\classes\Catagory', 10, 2);
        if (defined('WULACMF_INSTALLED')) {
            bind('artisan\getCommands', '&\cms\classes\cmd\StorageMigrateCommand');
        }

        return null;
    }
}

App::register(new CmsModule());
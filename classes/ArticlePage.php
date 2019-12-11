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
use cms\tag\model\CmsTag;
use wulaphp\app\App;
use wulaphp\io\Response;

/**
 * 文章页
 */
class ArticlePage extends ModelDoc {
    public function id() {
        return 'article';
    }

    public function name() {
        return '普通文章';
    }

    /**
     * 保存文章时
     *
     * @param array      $data
     * @param int|string $uid
     *
     * @return bool
     */
    public function save(&$data, $uid) {
        $id = $data['id'];
        unset($data['id']);
        $page    = $this->commonData($data);
        $channel = $data['channel']['chid'];
        $model   = $data['model']['id'];
        try {
            $db      = App::db();
            $chmodel = $db->queryOne('select url_pattern from {cms_channel_model} where enabled =1 and page_id=%d and model=%d LIMIT 0,1', $channel, $model);
            if (empty($page['url'])) {
                $page['url'] = $chmodel['url_pattern'];
            }
            if (!$chmodel) {
                $this->error = '模型配置不存在或未启用';

                return false;
            }
        } catch (\Exception $e) {
            $this->error = '无法连接数据库';

            return false;
        }
        //cms_page
        $table      = new CmsPage();
        $table->uid = $uid;
        if (!$id) {
            $page['create_time'] = time();
            $page['create_uid']  = intval($uid);
            //创建新页面
            $id = $table->newPage($page, $this->error);
            if (!$id) {
                return false;
            }
        } else {
            $page['update_time'] = time();
            $page['update_uid']  = intval($uid);
            $rst                 = $table->updatePage($id, $page, $this->error);
            if (!$rst) {
                return false;
            }
        }
        //最后一定要给id和ver赋值
        $data['id']  = $id;
        $data['ver'] = $page['ver'];

        return true;
    }

    /**
     * 后台列表加载时
     *
     * @param int|string $page
     * @param int|string $limit
     * @param int|string $chid
     * @param string     $mid
     * @param int|string $status
     * @param array      $sort
     *
     * @return array
     */
    public function loadData($page, $limit, $chid, $mid, $status, $sort) {
        $table                = new CmsPage();
        $where['CPF.channel'] = $chid;
        $where['CPF.model']   = $mid;
        $this->buildSearch($where);
        $q = $table->alias('CP')->select('CPF.page_id,CP.status,CPF.title,CPF.title2,CPF.author,CPF.source,CPF.flags,CPF.tags,CP.url,CU.nickname AS create_uid,UU.nickname AS update_uid,PU.nickname AS publisher,CP.create_time AS create_time,CPF.update_time AS update_time,CPF.publish_time AS publish_time')->page($page, $limit);
        if ($status < 10) {//从cms_page_rev表读取
            $where['CPF.status']   = $status;//0=>草稿,1=>待审核，2=>未核准
            $where['CP.status <>'] = 2;
            $q->join('{cms_page_rev} AS CPF', 'CPF.page_id = CP.id');
            $q->sort('CPF.ver', 'd');
            $q->field('CPF.ver', 'ver');
            $q->field('CPF.status', 'revStatus');
        } else {//从cms_page_field表读取
            $where['CP.status'] = $status - 10;//1,2
            $q->join('{cms_page_field} AS CPF', 'CPF.page_id = CP.id');
            $q->field('CP.ver', 'ver');
        }
        $q->join('{user} AS CU', 'CU.id = CP.create_uid');
        $q->join('{user} AS UU', 'UU.id = CPF.update_uid');
        $q->join('{user} AS PU', 'PU.id = CPF.publisher');
        $sort = $this->alterSort();
        $q->sort($sort['name'], $sort['dir'])->where($where);
        $total = $q->total('CP.id');

        $data = $q->toArray();
        foreach ($data as $k => &$v) {
            $v['create_time'] = date('Y-m-d H:i', $v['create_time']);
            $v['update_time'] = date('Y-m-d H:i', $v['update_time']);
            if ($v['publish_time']) {
                $v['publish_time'] = date('Y-m-d H:i', $v['publish_time']);
            } else {
                $v['publish_time'] = '';
            }
        }

        return $this->formatGridData($data, $total);
    }

    /**
     * 页面加载时
     *
     * @param array                         $page
     * @param \wulaphp\router\UrlParsedInfo $pageInfo
     */
    public function load(&$page, $pageInfo) {
        if (isset($page['content']) && $page['content']) {
            $page['content'] = str_replace(['<div><br></div>', '<div><br/></div>'], '', $page['content']);
            $page['content'] = apply_filter('cms\alterContent', $page['content']);
            if (!$page['content']) {
                return;
            }
            //处理分页
            if (!$pageInfo || $pageInfo->page == PHP_INT_MAX) {
                $page['content'] = @preg_replace('#(<hr\s*/?>|\[page\])#', '', $page['content']);

                return;
            }

            $contents        = @preg_split('#(<hr\s*/?>|\[page\])#', $page['content']);
            $pageInfo->total = count($contents);
            if ($pageInfo->total < $pageInfo->page) {
                Response::respond(404);
            } else {
                $page['content'] = CmsTag::useTag($contents[ $pageInfo->page - 1 ]);
                //使用内链
                $page['contents'] = $contents;
            }
        }
    }

    /**
     * 表格
     * @return array
     */
    public function gridCols() {
        $i               = 1;
        $cols            = parent::gridCols();
        $cols[0][ ++$i ] = [
            'field' => 'ver',
            'title' => '版本',
            'sort'  => true,
            'width' => 65
        ];
        $tpl             = null;
        $site            = self::getDefaultSite();
        if ($site) {
            $url = trailingslashit(($site['is_https'] ? 'https://' : 'http://') . $site['domain']);
            $tpl = '<div><a href="' . $url . '{{d.url}}" target="_blank">{{d.title}}</a></div>';
        }
        $cols[0][ ++$i ] = [
            'field'   => 'title',
            'title'   => '页面标题',
            'templet' => $tpl,
            'width'   => 200
        ];
        $cols[0][ ++$i ] = [
            'field' => 'title2',
            'title' => '页面副标题',
            'width' => 150
        ];
        $cols[0][ ++$i ] = [
            'field' => 'tags',
            'title' => '标签',
            'width' => 150
        ];
        $cols[0][ ++$i ] = [
            'field' => 'flags',
            'title' => '属性',
            'width' => 150
        ];
        $cols[0][ ++$i ] = [
            'field' => 'author',
            'title' => '作者',
            'width' => 100
        ];
        $cols[0][ ++$i ] = [
            'field' => 'source',
            'title' => '来源',
            'width' => 100
        ];
        $cols[0][ ++$i ] = [
            'field' => 'update_time',
            'title' => '最后更新时间',
            'sort'  => true,
            'width' => 150
        ];
        $cols[0][ ++$i ] = [
            'field' => 'update_uid',
            'title' => '最后更新者',
            'sort'  => true,
            'width' => 120
        ];
        $cols[0][ ++$i ] = [
            'field' => 'publish_time',
            'title' => '发布时间',
            'sort'  => true,
            'width' => 150
        ];
        $cols[0][ ++$i ] = [
            'field' => 'publisher',
            'title' => '发布者',
            'sort'  => true,
            'width' => 120
        ];
        $cols[0][ ++$i ] = [
            'field' => 'create_time',
            'title' => '创建时间',
            'sort'  => true,
            'width' => 150
        ];
        $cols[0][ ++$i ] = [
            'field' => 'create_uid',
            'title' => '创建者',
            'sort'  => true,
            'width' => 120
        ];

        return $cols;
    }

    private function alterSort() {
        $sort = rqst('sort');
        if ($sort && $sort['name']) {
            $sort['name'] = str_replace(['update_', 'create_', 'publish_'], [
                'CPF.update_',
                'CP.create_',
                'CPF.publish_'
            ], $sort['name']);
        }

        return $sort;
    }

    public function getForm($id, &$data) {
        $data['img_auto_dld']  = App::bcfg('downPic@cms');
        $data['scws_auto_get'] = App::bcfg('scwsEnabled@cms') && extension_loaded('scws');
        $data['desc_auto_get'] = App::bcfg('descEnabled@cms');

        $form              = new ArticlePageForm(true);
        $form->model_refid = $this->id();

        return $form;
    }
}
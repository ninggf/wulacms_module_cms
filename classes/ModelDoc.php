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

use backend\form\CheckboxField;
use backend\form\ComboxField;
use backend\form\DatepickerField;
use backend\form\FileUploaderField;
use backend\form\HiddenField;
use backend\form\MultipleCheckboxFiled;
use backend\form\PasswordField;
use backend\form\Plupload;
use backend\form\RadioField;
use backend\form\SelectField;
use backend\form\TextareaField;
use backend\form\TextField;
use backend\form\TimepickerField;
use backend\form\WysiwygField;
use cms\classes\form\DefaultPageForm;
use cms\classes\model\CmsPage;
use cms\classes\scws\Scwser;
use media\classes\ImageTool;
use media\Media1Module;
use wulaphp\app\App;
use wulaphp\db\sql\Condition;

/**
 * 内容模型加载器.
 *
 * @package cms\classes
 */
abstract class ModelDoc {
    /**
     * @var \wulaphp\db\DatabaseConnection
     */
    private          $db;
    protected static $MODELS = false;
    protected        $error;

    /**
     * @return string
     */
    public abstract function id();

    /**
     * @return string
     */
    public abstract function name();

    /**
     * @return string|\wulaphp\mvc\view\JsonView|array
     */
    public function last_error() {
        return $this->error;
    }

    /**
     * 是否是内置的模型
     * @return bool
     */
    public function isNative() {
        return true;
    }

    /**
     * 动态表单（后期添加的）
     *
     * @param int|string $id 内容ID
     *
     * @return \wulaphp\form\FormTable|null
     */
    public final function getDynamicForm($id = 0) {
        try {
            $form = new DefaultPageForm($this->id());
        } catch (\Exception $e) {
            $form = null;
        }

        return $form;
    }

    /**
     * @param string|int $id
     * @param array      $data
     *
     * @return \wulaphp\form\FormTable
     */
    public function getForm($id, &$data) {
        return null;
    }

    /**
     * 编辑模板.
     *
     * @param string|int $id   内容ID
     * @param array      $data 填充模板的变量
     *
     * @return string|null
     */
    public function getTpl($id, &$data) {
        return null;
    }

    public function getPageTpl() {
        return '';
    }

    public function getListTpl() {
        return '';
    }

    /**
     * 加载数据
     *
     * @param string|int $page
     * @param string|int $limit
     * @param string|int $chid
     * @param string     $mid
     * @param string|int $status
     * @param array      $sort
     *
     * @return array
     */
    public function loadData($page, $limit, $chid, $mid, $status, $sort) {
        return ['code' => 0, 'count' => 0, 'data' => []];
    }

    /**
     * 获取模型的列表头.
     *
     * @return array
     */
    public function gridCols() {
        return [
            [
                0   => [
                    'type'  => 'checkbox',
                    'fixed' => 'left',
                    'width' => 30,
                ],
                1   => [
                    'field' => 'page_id',
                    'title' => 'ID',
                    'fixed' => 'left',
                    'width' => 80,
                    'sort'  => true
                ],
                200 => [
                    'title'   => '',
                    'fixed'   => 'right',
                    'align'   => 'center',
                    'width'   => 80,
                    'toolbar' => '#' . $this->id() . 'Toolbar'
                ]
            ]
        ];
    }

    /**
     * 工具栏按钮.
     *
     * @return array
     */
    public function toolBar() {
        $passport = whoami('admin');
        $btns     = [];
        if ($passport->cando('edit:site/page')) {
            $btns[0] = '<a href="' . App::url('cms/site/page/edit') . '/" data-tab="&#xe653;" class="btn btn-xs btn-primary" lay-event="edit" data-title="编辑页面[{page_id}]"><i class="fa  fa-pencil-square-o"></i></a>';
        }
        if ($passport->cando('del:site/page')) {
            $btns[20] = '<a href="' . App::url('cms/site/page/del') . '/" data-ajax data-confirm="你真的要删除该内容吗?" data-title="删除确认" class="btn btn-xs btn-danger" lay-event="del"><i class="fa fa-trash-o"></i></a>';
        }

        return $btns;
    }

    /**
     * 加载内容.
     *
     * @param array                         $page     页面数据
     * @param \wulaphp\router\UrlParsedInfo $pageInfo 页面信息
     */
    public function load(&$page, $pageInfo) {
    }

    /**
     * 保存内容.
     *
     * @param array      $data 页面数据
     * @param int|string $uid  用户ID
     *
     * @return bool
     */
    public function save(&$data, $uid) {
        return true;
    }

    /**
     * 删除内容.
     *
     * @param string|int|array $id
     * @param string|int       $uid
     *
     * @return bool
     */
    public function delete($id, $uid) {
        $db    = $this->transDb();
        $table = new CmsPage($db);
        if (is_array($id)) {
            $page = $id;
        } else {
            $page = $table->loadFields($id, false);
        }

        $rst = $db->cud('DELETE FROM {cms_page} WHERE id = %d AND status = 2', $page['id']);
        if ($rst) {
            $data_files = $db->query('select data_file from {cms_page_rev} where page_id = %d', $page['id']);
            foreach ($data_files as $data_file) {
                if (!$table->deleteFile($data_file['data_file'])) {
                    return false;
                }
            }
            $rst = $rst && $db->cudx('DELETE FROM {cms_page_rev} WHERE page_id = %d', $page['id']);
            $rst = $rst && $db->cudx('DELETE FROM {cms_page_field} WHERE page_id = %d', $page['id']);
            $rst = $rst && $db->cudx('DELETE FROM {cms_page_tag} WHERE page_id = %d', $page['id']);
            $rst = $rst && $db->cudx('DELETE FROM {cms_page_flag} WHERE page_id = %d', $page['id']);
            $rst = $rst && $db->cudx('DELETE FROM {cms_router} WHERE id = %d', $page['id']);
        }

        return $rst;
    }

    /**
     * 放入回收站
     *
     * @param array|int|string $id
     * @param int|string       $uid
     *
     * @return bool
     */
    public function recycle($id, $uid) {
        $db = $this->transDb();
        if (is_array($id)) {
            $page = $id;
        } else {
            $table = new CmsPage($db);
            $page  = $table->loadFields($id, false);
        }
        $rst = $db->cudx('UPDATE {cms_page} SET origin_status = status, status=2 WHERE id = %d', $page['id']);
        $rst = $rst && $db->cudx('UPDATE {cms_page_field} SET status=2 WHERE page_id = %d', $page['id']);

        return $rst;
    }

    /**
     * 从回收站还原.
     *
     * @param string|int|array $id
     * @param string|int       $uid
     *
     * @return bool
     */
    public function restore($id, $uid) {
        $db = $this->transDb();
        if (is_array($id)) {
            $page = $id;
        } else {
            $table = new CmsPage($db);
            $page  = $table->loadFields($id, false);
        }
        $os = $db->query('select origin_status from {cms_page} where id = %d', $page['id']);
        $os = $os && $os[0] ? $os[0]['origin_status'] : 0;

        if (!$db->cudx('UPDATE {cms_page_field} SET status = %d WHERE page_id = %d', $os, $page['id'])) {
            return false;
        }

        return true;
    }

    /**
     * 获取通用页面字段
     *
     * @param array $data
     *
     * @return array
     */
    protected function commonData(&$data) {
        $page['noindex']     = intval(aryget('noindex', $data, 0));
        $page['expire']      = intval($data['expire']);
        $page['channel']     = $data['channel']['chid'];
        $page['model']       = $data['model']['id'];
        $page['model_refid'] = $data['model']['refid'];
        $page['ver']         = irqst('ver');
        if ($page['model'] == 1) {
            $page['path'] = $data['channel']['path'] . $data['store_path'] . '/';
        } else {
            $page['path'] = $data['channel']['path'];
        }

        //提取关键词
        if ((!isset($data['keywords']) || !$data['keywords']) && isset($data['title']) && $data['title'] && $data['scws_auto_get']) {
            $dict = App::cfg('scwsDict@cms');
            $cnt  = App::icfgn('scwsCnt@cms', 10);
            $keys = Scwser::scws($data['title'], $cnt, $dict);
            if ($keys) {
                $data['keywords'] = implode(',', $keys);
            }
        }

        //提交描述
        if ((!isset($data['description']) || !$data['description']) && isset($data['content']) && $data['content'] && $data['desc_auto_get']) {
            $content = cleanhtml2simple($data['content']);
            $cnt     = App::icfgn('descCnt@cms', 10);
            if ($content) {
                $data['description'] = mb_substr($content, 0, $cnt);
            }
        }

        //下载远程图片
        if (isset($data['content']) && $data['content'] && $data['img_auto_dld']) {
            $exclude_urls = Media1Module::get_media_domains([WWWROOT_DIR]);
            if (class_exists('\media\classes\ImageTool') && $exclude_urls && $exclude_urls[0] != WWWROOT_DIR) {
                $content  = $data ['content'];
                $uploader = Plupload::getUploader();
                foreach ($exclude_urls as $k => $eurl) {
                    $exclude_urls[ $k ] = parse_url($eurl, PHP_URL_HOST);
                }
                if (preg_match_all('#<img.+?src\s*=\s*["\'](https?[^"\']+?)["\'].*?>#im', $content, $ms)) {
                    $images = [];
                    foreach ($ms [1] as $idx => $m) {
                        $info = parse_url($m, PHP_URL_HOST);
                        if ($info && !in_array($info, $exclude_urls)) {
                            $images [ $m ] = [$m, null];
                        }
                    }
                    if ($images) {
                        $watermark        = null;
                        $enable_watermark = App::bcfg('enable_watermark@media', false);
                        if ($enable_watermark) {
                            $watermark = App::cfg('watermark@media');
                            if ($watermark) {
                                $watermark = WWWROOT . $watermark;
                            }
                        }
                        if ($watermark && is_file($watermark)) {
                            $watermarkcfg = [
                                $watermark,
                                App::cfg('watermark_pos@media', 'br'),
                                App::cfg('watermark_min_size@media')
                            ];
                        } else {
                            $watermarkcfg = false;
                        }

                        $resize_img = App::cfg('resize_img@cms');
                        if ($resize_img) {
                            $resize = explode('x', $resize_img);
                        } else {
                            $resize = [];
                        }
                        $rst = ImageTool::download($images, $uploader, App::icfgn('down_timeout@cms', 30), $watermarkcfg, $resize);
                        foreach ($rst as $old => $new) {
                            $content = str_replace($old, the_media_src($new ['url']), $content);
                        }
                        $data ['content'] = $content;
                    }

                }
            }
        }

        return array_merge($data, $page);
    }

    /**
     * 构建查询条件
     *
     * @param array $where
     */
    protected function buildSearch(&$where) {
        $q = rqst('q');
        if ($q) {
            if (is_numeric($q)) {
                $where['CP.id'] = $q;
            } else {
                $wh = Condition::parseSearchExpression($q, [
                    '标题'  => 'CPF.title',
                    '副标题' => 'CPF.title2',
                    '作者'  => 'CPF.author',
                    '来源'  => 'CPF.source',
                    '标签'  => 'CPF.tags',
                    '属性'  => 'CPF.flags',
                    'url' => 'CP.url'
                ]);
                if ($wh) {
                    foreach ($wh as $k => $h) {
                        $where[ $k ] = $h;
                    }
                }
            }
        }
    }

    /**
     * 格式化表格数据。
     *
     * @param array      $data
     * @param int|string $total
     *
     * @return array
     */
    protected function formatGridData($data, $total) {
        return ['code' => 0, 'data' => $data, 'count' => $total];
    }

    /**
     * 获取一个处理事务中的数据库连接。
     *
     * @param \wulaphp\db\DatabaseConnection $db
     *
     * @return \wulaphp\db\DatabaseConnection
     */
    public final function transDb($db = null) {
        if ($db) {
            $this->db = $db;
        }
        if ($this->db) {
            return $this->db;
        }
        try {
            $this->db = App::db();

            return $this->db;
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * 获取$model_id对应的模型实例.
     *
     * @param string $model_id
     *
     * @return \cms\classes\ModelDoc|null
     */
    public final static function getDoc($model_id) {
        static $docs = false;
        if ($docs === false) {
            $docs = apply_filter('cms\initModel', []);
        }
        if (isset($docs[ $model_id ])) {
            return $docs[ $model_id ];
        }

        $doc               = new DefaultPage($model_id);
        $docs[ $model_id ] = $doc;

        return $doc;
    }

    /**
     * 获取可用的表单组件.
     *
     * @return array
     */
    public final static function getFormControlls() {
        static $fields = false;
        if ($fields === false) {
            $fields                                 = [];
            $fields[ TextField::class ]             = new TextField('', null);
            $fields[ TextareaField::class ]         = new TextareaField('', null);
            $fields[ SelectField::class ]           = new SelectField('', null);
            $fields[ RadioField::class ]            = new RadioField('', null);
            $fields[ CheckboxField::class ]         = new CheckboxField('', null);
            $fields[ MultipleCheckboxFiled::class ] = new MultipleCheckboxFiled('', null);
            $fields[ ComboxField::class ]           = new ComboxField('', null);
            $fields[ DatepickerField::class ]       = new DatepickerField('', null);
            $fields[ TimepickerField::class ]       = new TimepickerField('', null);
            $fields[ FileUploaderField::class ]     = new FileUploaderField('', null);
            $fields[ HiddenField::class ]           = new HiddenField('', null);
            $fields[ PasswordField::class ]         = new PasswordField('', null);
            $fields[ WysiwygField::class ]          = new WysiwygField('', null);
            $fields                                 = apply_filter('cms\regFormControll', $fields);
        }

        return $fields;
    }

    /**
     * 获取模型表格头。
     * @return array
     */
    public final static function getGridCols() {
        static $models = false, $cols = [], $toolbars = [];
        if ($models === false) {
            try {
                $db     = App::db();
                $models = $db->query('SELECT refid FROM {cms_model} WHERE id > 1');
                foreach ($models as $m) {

                    $doc                     = self::getDoc($m['refid']);
                    $cols[ $m['refid'] ]     = $doc->gridCols();
                    $toolbars[ $m['refid'] ] = $doc->toolBar();
                }
            } catch (\Exception $e) {
                $models = [];
            }
        }

        return [$cols, $toolbars];
    }

    /**
     * 可用控件数据源列表.
     *
     * @return array
     */
    public final static function getDataSources() {
        static $dss = [];
        if (!$dss) {
            $dss['json']  = 'JSON';
            $dss['text']  = '文本行';
            $dss['param'] = '参数';
            $dss['table'] = '数据库表';
            $dss          = apply_filter('cms\regFieldDatasource', $dss);
        }

        return $dss;
    }

    /**
     * 获取默认网站信息.
     *
     * @return array
     */
    public final static function getDefaultSite() {
        static $site = null;
        if ($site === null) {
            try {
                $db   = App::db();
                $site = $db->queryOne('SELECT * FROM {cms_domain} WHERE is_default = 1 LIMIT 0,1');
            } catch (\Exception $e) {
                $site = [];
            }
        }

        return $site;
    }
}
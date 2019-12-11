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

use cms\classes\model\CmsModelTable;
use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class ArticlePageForm extends FormTable {
    use JQueryValidator;
    public $table = null;
    public $model_refid;
    /**
     * @var \backend\form\HiddenField
     * @type int
     */
    public $id = 0;
    /**
     * 标题
     * @var \backend\form\TextField
     * @type string
     * @required
     * @layout 2,col-xs-8
     */
    public $title = '';
    /**
     * 副标题
     * @var \backend\form\TextField
     * @type string
     * @layout 2,col-xs-4
     */
    public $title2 = '';

    /**
     * 页面属性
     * @var \backend\form\MultipleCheckboxFiled
     * @type list
     * @dsCfg ::modelFlags
     * @layout 3,col-xs-8
     * @option {"inline":1}
     */
    public $flags = '';
    /**
     * 页面标签(多个标签用','分隔)
     * @var \backend\form\TextField
     * @type string
     * @layout 3,col-xs-4
     */
    public $tags = '';

    /**
     * 页面URL
     * @var \backend\form\TextField
     * @type string
     * @layout 4,col-xs-4
     * @note   不填写自动将自动生成.
     */
    public $url = '';
    /**
     * 页面模板
     * @var \backend\form\TextField
     * @type string
     * @layout 4,col-xs-4
     * @note   不填写将使用栏目设置
     */
    public $template_file = '';
    /**
     * 页面缓存
     * @var \backend\form\TextField
     * @type int
     * @pattern (/^(-1|0|[1-9]\d*)$/) => 只能是大于-1的整数.
     * @layout 4,col-xs-4
     * @note   -1不缓存0默认,单位秒.
     */
    public $expire = 0;

    /**
     * 页面插图
     * @var \backend\form\FileUploaderField
     * @type string
     * @layout 5,col-xs-12
     * @option {"skin":"file","exts":"jpg,gif,png,jpeg","width":120,"height":90,"noWater":1,"maxFileSize":"10kb","url":"cms/site/upload-img"}
     */
    public $image;
    /**
     * 页面插图二
     * @var \backend\form\FileUploaderField
     * @type string
     * @layout 5,col-xs-4
     * @option {"exts":"jpg,gif,png,jpeg","width":120,"height":90,"noWater":1,"maxFileSize":"1mb"}
     */
    public $image1;
    /**
     * 页面插图三
     * @var \backend\form\FileUploaderField
     * @type string
     * @layout 5,col-xs-4
     * @option {"exts":"jpg,gif,png,jpeg","width":120,"height":90,"noWater":1,"maxFileSize":"1mb"}
     */
    public $image2;

    /**
     * 页面内容
     * @var \backend\form\WysiwygField
     * @type string
     * @layout 30,col-xs-12
     * @option {"height":500,"btns":"font,size,color,style,link,img,pager,preview"}
     */
    public $content;
    /**
     * 自动下载远程图片
     * @var \backend\form\CheckboxField
     * @type bool
     * @layout 30,col-xs-6
     */
    public $img_auto_dld;

    /**
     * 作者
     * @var \backend\form\TextField
     * @type string
     * @layout 50,col-xs-3
     */
    public $author;
    /**
     * 来源
     * @var \backend\form\TextField
     * @type string
     * @layout 50,col-xs-3
     */
    public $source;
    /**
     * 发布时间
     * @var \backend\form\DatepickerField
     * @type string
     * @layout 50,col-xs-3
     */
    public $publish_day;

    /**
     * &nbsp;
     * @var \backend\form\TimepickerField
     * @type string
     * @layout 50,col-xs-3
     */
    public $publish_hm;

    /**
     * 阅读数
     * @var \backend\form\TextField
     * @type int
     * @digits
     * @layout 60,col-xs-3
     */
    public $view = 0;
    /**
     * 评论数
     * @var \backend\form\TextField
     * @type int
     * @digits
     * @layout 60,col-xs-3
     */
    public $cmts = 0;
    /**
     * 顶次数
     * @var \backend\form\TextField
     * @type int
     * @digits
     * @layout 60,col-xs-3
     */
    public $dig = 0;
    /**
     * 踩次数
     * @var \backend\form\TextField
     * @type int
     * @digits
     * @layout 60,col-xs-3
     */
    public $dig1 = 0;
    /**
     * 关键词
     * @var \backend\form\TextField
     * @type string
     * @layout 70,col-xs-6
     */
    public $keywords = '';
    /**
     * 相关页面(多个页面使用','分隔)
     * @var \backend\form\TextField
     * @type string
     * @layout 70,col-xs-6
     */
    public $related_pages;
    /**
     * 自动提取
     * @var \backend\form\CheckboxField
     * @type bool
     * @layout 70,col-xs-6
     */
    public $scws_auto_get;
    /**
     * 描述
     * @var \backend\form\TextareaField
     * @type string
     * @layout 80,col-xs-12
     */
    public $description = '';
    /**
     * 自动提取
     * @var \backend\form\CheckboxField
     * @type bool
     * @layout 80,col-xs-6
     */
    public $desc_auto_get;

    public function alterFieldOptions($name, &$options) {
        if ($this->_tableData && $this->_tableData['id'] && $name == 'url') {
            $options['readonly'] = true;
        }
        if ($name == 'img_auto_dld') {
            $options['disabled'] = true;
        }
        if ($name == 'scws_auto_get' && !extension_loaded('scws')) {
            $options['disabled'] = true;
        }
    }

    public function modelFlags() {
        $id    = $this->model_refid ? $this->model_refid : 'article';
        $model = new CmsModelTable();
        $flags = $model->getFlags($id);

        return $flags;
    }
}
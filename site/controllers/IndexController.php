<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\site\controllers;

use backend\classes\IFramePageController;
use cms\classes\ModelDoc;
use wulaphp\app\App;

/**
 * Class IndexController
 * @package cms\site\controllers
 * @acl     m:site/page
 */
class IndexController extends IFramePageController {
    /**
     * 首页
     * @return \wulaphp\mvc\view\View
     */
    public function index() {
        $data['canMgCh']        = $this->passport->cando('mc:site/page');
        $data['canApprove']     = $this->passport->cando('ap:site/page');
        $data['canPublish']     = $this->passport->cando('pb:site/page');
        $data['canClearCC']     = $this->passport->cando('hc:site/page');
        $data['canDelPage']     = $this->passport->cando('del:site/page');
        $data['canEditPage']    = $this->passport->cando('edit:site/page');
        $gridCfg                = ModelDoc::getGridCols();
        $data['modelGridCols']  = $gridCfg[0];
        $data['modelToolbars']  = $gridCfg[1];
        $data['approveEnabled'] = App::bcfg('approveEnabled@cms');
        $data['cmsMainModule']  = '{/}' . App::res('cms/main');

        return $this->render($data)->addStyle(App::res('cms/style.css'));
    }

    /**
     * 栏目树
     *
     * @param string $id
     *
     * @return array
     */
    public function channelNode($id = '') {
        $id = intval($id);
        try {
            $canApprove = $this->passport->cando('ap:site/page');
            $db         = App::db();
            if ($canApprove) {
                $chs = $db->query('SELECT CPF.title2 as name,CPF.page_id as id,CP.status,channel AS upid FROM {cms_channel} AS CH LEFT JOIN {cms_page_field} AS CPF ON CPF.page_id = CH.page_id LEFT JOIN {cms_page} AS CP ON CP.id = CH.page_id WHERE channel = %d ORDER BY CP.status ASC, display_sort ASC', $id);
            } else {
                $chs = $db->query('SELECT CPF.title2 as name,CPF.page_id as id,CP.status,channel AS upid FROM {cms_channel} AS CH LEFT JOIN {cms_page_field} AS CPF ON CPF.page_id = CH.page_id LEFT JOIN {cms_page} AS CP ON CP.id = CH.page_id WHERE CP.status = 1 AND channel = %d ORDER BY display_sort ASC', $id);
            }
            foreach ($chs as $k => $ch) {
                $chs[ $k ]['channel']  = $id;
                $chs[ $k ]['isParent'] = true;
                if (!$ch['status']) {
                    $chs[ $k ]['name'] = '<font class="text-muted">' . $chs[ $k ]['name'] . '</font>';
                } else if ($ch['status'] == '2') {
                    $chs[ $k ]['name'] = '<font class="text-danger">' . $chs[ $k ]['name'] . '</font>';
                }
                $models              = $db->query('SELECT CM.name,CM.id,CCM.enabled,CM.refid FROM {cms_channel_model} AS CCM LEFT JOIN {cms_model} AS CM ON CCM.model = CM.id WHERE CM.creatable = 1 AND CCM.page_id = ' . $ch['id'] . ' ORDER BY CM.id DESC');
                $chs[ $k ]['models'] = $models;
            }

            return $chs;
        } catch (\Exception $e) {

        }

        return [];
    }
}

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

use backend\classes\BackendController;
use cms\classes\ModelDoc;
use wulaphp\app\App;

/**
 * Class DataController
 * @package cms\site\controllers
 * @acl     m:site/page
 */
class DataController extends BackendController {
    /**
     * @param $page
     * @param $limit
     * @param $chid
     * @param $mid
     * @param $status
     * @param $sort
     *
     * @return array
     * @throws \Exception
     */
    public function indexPost($page, $limit, $chid, $mid, $status = '', $sort = []) {
        if (!$chid || !$mid) {
            return ['code' => 0, 'count' => 0, 'msg' => '', 'data' => []];
        } else {
            if ($status == '2' && !$this->passport->cando('del:site/page')) {//删除权限
                return ['code' => 0, 'count' => 0, 'data' => [], 'msg' => '你无权查看回收站里的内容'];
            } else if ($status == '3' && !$this->passport->cando('ap:site/page')) {//审核权限
                return ['code' => 0, 'count' => 0, 'data' => [], 'msg' => '你无权查看待审核内容'];
            } else if ($status == '4' && !$this->passport->cando('pb:site/page')) {//发布权限
                return ['code' => 0, 'count' => 0, 'msg' => '你无权查看待发布内容', 'data' => []];
            }
            $db    = App::db();
            $model = $db->queryOne('SELECT id FROM {cms_model} WHERE refid = %s', $mid);
            if (!$model) {
                return ['code' => 0, 'count' => 0, 'msg' => '内容模型好像不存在了', 'data' => []];

            }
            $doc  = ModelDoc::getDoc($mid);
            $data = $doc->loadData($page, $limit, $chid, $model['id'], $status, $sort);

            return $data;
        }
    }
}
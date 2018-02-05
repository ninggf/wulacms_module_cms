<?php
/**
 * //                            _ooOoo_
 * //                           o8888888o
 * //                           88" . "88
 * //                           (| -_- |)
 * //                            O\ = /O
 * //                        ____/`---'\____
 * //                      .   ' \\| |// `.
 * //                       / \\||| : |||// \
 * //                     / _||||| -:- |||||- \
 * //                       | | \\\ - /// | |
 * //                     | \_| ''\---/'' | |
 * //                      \ .-\__ `-` ___/-. /
 * //                   ___`. .' /--.--\ `. . __
 * //                ."" '< `.___\_<|>_/___.' >'"".
 * //               | | : `- \`.;`\ _ /`;.`/ - ` : | |
 * //                 \ \ `-. \_ __\ /__ _/ .-` / /
 * //         ======`-.____`-.___\_____/___.-`____.-'======
 * //                            `=---='
 * //
 * //         .............................................
 * //                  佛祖保佑             永无BUG
 * DEC : cms 页面管理
 * User: wangwei
 * Time: 2018/2/5 下午1:59
 */

namespace cms\controllers;

use backend\classes\IFramePageController;
use cms\classes\model\CmsDomain;

class PageController extends IFramePageController {

	public function domain() {
		$data = [];

		return $this->render($data);
	}

	public function domain_data($type = '', $q = '', $count = '', $pager, $sort) {
		$page      = $pager['page'];
		$page_size = $pager['size'];
		$model     = new CmsDomain();
		$where     = ['id >=' => 1];
		if ($type) {
			$where['type'] = $type;
		}
		if ($q) {
			$where1['filename LIKE'] = '%' . $q . '%';
			$where[]                 = $where1;
		}
		$query = $model->select('*')->where($where)->limit(($page - 1) * $page_size, $page_size)->sort($sort['name'], $sort['dir']);
		$rows  = $query->toArray();
		$total = '';
		if ($count) {
			$total = $query->total('id');
		}
		$data['rows']  = $rows;
		$data['total'] = $total;

		return view($data);
	}

	public function edit($id) {
		$hd = opendir ( THEME_PATH  );
		$themes = array ();
		if ($hd) {
			while ( ($f = readdir ( $hd )) != false ) {
				if ($f != '.' && $f != '..' && is_dir ( THEME_PATH  . $f )) {
					$themes [] = $f;
				}
			}
			closedir ( $hd );
		}
		return $this->render();
	}
}
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
 * DEC : cms_domain 表单绘制
 * User: wangwei
 * Time: 2018/2/5 下午3:24
 */

namespace cms\classes\form;

use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class CmsDomainForm extends FormTable {
	use JQueryValidator;
	/**
	 * 域名
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 1,col-xs-8
	 */
	public $domain;
	/**
	 * 网站名称
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 1,col-xs-4
	 */
	public $name;
	/**
	 * 是否是默认网站
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"不是","1":"是"}
	 * @layout 2,col-xs-4
	 */
	public $is_default = 0;

	/**
	 * 强制HTTPS
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"不是","1":"是"}
	 * @layout 2,col-xs-4
	 */
	public $is_https = 0;

	/**
	 * 是否离线
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"不是","1":"是"}
	 * @layout 2,col-xs-4
	 */
	public $offline = 0;

	/**
	 * 模板目录
	 * @var \backend\form\SelectField
	 * @type string
	 * @dsCfg ::getThemes
	 * @layout 4,col-xs-4
	 */
	public $theme;

	/**
	 * 主页模板
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 4,col-xs-4
	 */
	public $tpl;
	/**
	 * 默认缓存时间（单位秒）
	 * @var \backend\form\TextField
	 * @type int
	 * @required
	 * @digits
	 * @layout 4,col-xs-4
	 */
	public $expire = 0;
	/**
	 * 主页标题
	 * @var \backend\form\TextField
	 * @type string
	 * @required
	 * @layout 5,col-xs-12
	 */
	public $title;
	/**
	 * 网站关键词
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 6,col-xs-12
	 */
	public $keywords;
	/**
	 * 网站描述
	 * @var \backend\form\TextareaField
	 * @type string
	 * @layout 7,col-xs-12
	 */
	public $description;

	public function getThemes() {
		$hd     = opendir(THEME_PATH);
		$themes = [];
		if ($hd) {
			while (($f = readdir($hd)) != false) {
				if ($f != '.' && $f != '..' && is_dir(THEME_PATH . $f)) {
					$themes [ $f ] = $f;
				}
			}
			closedir($hd);
		}

		return $themes;
	}

	/**
	 * 更新 cms_domain.
	 *
	 * @param mixed $con  条件
	 * @param array $data 更新数据.
	 *
	 * @return bool
	 */
	public function updateDomain($con, $data) {
		if (is_array($con)) {
			$where = $con;
		} else {
			$where['id'] = $con;
		}

		return $this->update($data, $where);
	}

	/**
	 * 新增 cms_domain.
	 *
	 * @param array $data 新增数据.
	 *
	 * @return bool
	 */
	public function indsertDomain($data) {
		if (!$data) {
			return false;
		}

		return $this->insert($data);
	}

	/**
	 * 删除操作
	 *
	 * @param mixed $con 条件
	 *
	 * @return bool
	 */
	public function delDomain($con) {
		if (is_array($con)) {
			$where = $con;
		} else {
			$where['id'] = $con;
		}

		return $this->delete($where);
	}
}
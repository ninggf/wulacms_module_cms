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
	 * @note   设置后只能通过此域名(如非80，443访问需带端口)访问管理后台。为了安全请尽量与前台域名不同。
	 * @layout 1,col-xs-12
	 */
	public $domain;
	/**
	 * 是否是默认网站
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"不是","1":"是"}
	 * @layout 2,col-xs-6
	 */
	public $is_default = 0;

	/**
	 * 是否默认https
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"不是","1":"是"}
	 * @layout 2,col-xs-6
	 */
	public $is_https = 0;

	/**
	 * 模板
	 * @var \backend\form\SelectField
	 * @type string
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {}
	 * @layout 4,col-xs-12
	 */
	public $theme;

	/**
	 * 修改字段属性.
	 *
	 * @param string $name    字段名
	 * @param array  $options 字段属性.
	 */
	public function alterFieldOptions($name, &$options) {
		if ($name == 'theme') {
			$hd     = opendir(THEME_PATH);
			$themes = array();
			if ($hd) {
				while (($f = readdir($hd)) != false) {
					if ($f != '.' && $f != '..' && is_dir(THEME_PATH . $f)) {
						$themes [ $f ] = $f;
					}
				}
				closedir($hd);
			}
			$options['dsCfg'] = $themes;
		}
	}

	/**
	 * 更新 cms_domain.
	 *
	 * @param mixed $con  条件
	 * @param array $data 更新数据.
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
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
 * DEC :
 * User: wangwei
 * Time: 2018/2/5 下午3:24
 */

namespace cms\classes\form;

use wulaphp\form\FormTable;
use wulaphp\validator\JQueryValidator;

class CmsDomainForm extends FormTable {
	use JQueryValidator;
	/**
	 * 管理域名
	 * @var \backend\form\TextField
	 * @type string
	 * @note 设置后只能通过此域名(如非80，443访问需带端口)访问管理后台。为了安全请尽量与前台域名不同。
	 */
	public $domain;
	/**
	 * 是否默认
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"是","1":"不是"}
	 */
	public $is_default=0;

	/**
	 * 是否默认https
	 * @var \backend\form\SelectField
	 * @type int
	 * @dataSource \wulaphp\form\providor\LineDataProvidor
	 * @dsCfg {"0":"是","1":"不是"}
	 */
	public $is_https=0;

	/**
	 * 修改字段属性.
	 *
	 * @param string $name    字段名
	 * @param array  $options 字段属性.
	 */
	public function alterFieldOptions($name, &$options) {
		if($name == 'is_https') {
			$options['dsCfg'] = ['0' => '', '1' => ''];
		}
	}

}
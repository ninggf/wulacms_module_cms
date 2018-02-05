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
 * Time: 2018/2/1 上午11:07
 */

namespace cms\classes;

use wulaphp\mvc\view\View;
use wulaphp\router\IURLDispatcher;
use wulaphp\router\Router;
use wulaphp\router\UrlParsedInfo;

class CmsDispatcher  implements IURLDispatcher {
	/**
	 * 分发URL.
	 * 一旦有一个分发器返回View实例，则立即返回，停止分发其它的.
	 *
	 * @param string        $url        URL.
	 * @param Router        $router     路由器.
	 * @param UrlParsedInfo $parsedInfo URL解析信息.
	 *
	 * @return View View 实例.
	 */
	function dispatch($url, $router, $parsedInfo) {
		// TODO: Implement dispatch() method.
//		if ($url == 'hello.html') {// hello.html
//			return template('hello.tpl');
//		} else if (preg_match('#^hello/(\d+)$#', $url, $ms)) {// hello/<id>
//			$data['id'] = $ms[1];
//
//			return template('hello1.tpl', $data);
//		}

		return null;
	}

}
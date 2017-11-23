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
/**
 * 动态页（路由）
 */
class DynamicPage extends ModelDoc {
	public function id() {
		return 'dynamic';
	}

	public function name() {
		return '动态页';
	}
}
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
 * 栏目页
 */
class Catagory extends ModelDoc {
	public function id() {
		return 'catagory';
	}

	public function name() {
		return '栏目';
	}

}
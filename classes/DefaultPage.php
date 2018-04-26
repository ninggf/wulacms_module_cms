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

class DefaultPage extends ArticlePage {
	private $id;
	private $name;

	public function __construct($id, $name = '') {
		$this->id   = $id;
		$this->name = $name;
	}

	public function isNative() {
		return false;
	}

	public function id() {
		return $this->id;
	}

	public function name() {
		return $this->name;
	}
}
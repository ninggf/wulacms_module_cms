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

abstract class ModelDoc {

	public abstract function id();

	public abstract function name();

	public function load($id, $page) {
		return null;
	}

	public function loads($ids) {
		return null;
	}

	public function save($doc) {
		return null;
	}

	public function delete($id) {
		return null;
	}
}
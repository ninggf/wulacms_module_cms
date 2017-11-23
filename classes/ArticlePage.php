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
 * 文章页
 */
class ArticlePage extends ModelDoc {
	public function id() {
		return 'article';
	}

	public function name() {
		return '文章';
	}
}
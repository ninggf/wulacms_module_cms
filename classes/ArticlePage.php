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

use wulaphp\form\FormTable;

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

	public function getForm($id, &$data) {
		return new ArticlePageForm(true);
	}
}

class ArticlePageForm extends FormTable {
	public $table = null;
	/**
	 * 正文
	 * @var \backend\form\WysiwygField
	 * @type string
	 * @layout 1,col-xs-12
	 * @option {"height":150,"placeholder":"placeholder"}
	 */
	public $content;
}
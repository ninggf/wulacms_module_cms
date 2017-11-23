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

class WebPage {
	public $title;
	public $title2;
	public $keywords;
	public $description;
	public $image;
	public $source;
	public $author;
	public $template_file;
	public $content;
	public $url;
	public $data = [];
	public $currentPage;
	public $totalPage;
	public $related_pages;
	public $channel;
	public $path;
	public $tags;
	public $flags;
}
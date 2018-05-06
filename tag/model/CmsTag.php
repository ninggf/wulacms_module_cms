<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\tag\model;

use cms\classes\scws\Scwser;
use wulaphp\app\App;
use wulaphp\form\FormTable;

class CmsTag extends FormTable {
	/**
	 * 替换content中的标签为内链.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public static function useTag($content) {
		if ($content) {
			$content = preg_replace('#<br[/\s]*>#', '', $content);
			$content = preg_replace('#(</?div>)\1+#', '\1', $content);
		}
		if (extension_loaded('scws') && $content && App::bcfg('tagEnabled@cms')) {
			$tmp    = preg_replace('#<[^>]+>#', '', $content);
			$tmp    = preg_replace('#&.+?;#', '', $tmp);
			$rcount = App::icfgn('tags_count@cms', 10);
			if (!$rcount) {
				$rcount = 10;
			}
			$table = new CmsTag();
			$file  = TMP_PATH . 'tag-dict.xdb';
			if (!is_file($file)) {
				$file = TMP_PATH . 'tag-dict.txt';
			}
			$tags = Scwser::scws($tmp, $rcount, $file);
			$tags = $table->select('url,title,tag')->where(['tag IN' => $tags])->desc('sort')->toArray();
			if ($tags) {
				$aid     = 0;
				$oldTags = [];
				//1. 将a标签替换为a-id@$aid
				$content = preg_replace_callback('#<a.+?</a>#msi', function ($ms) use (&$aid, &$oldTags) {
					$r             = "<!--a-id@{$aid}-->";
					$oldTags[ $r ] = $ms[0];
					$aid++;

					return $r;
				}, $content);
				//2. 将标签属性替换为a-id@$aid
				$content = preg_replace_callback('#<[a-z][a-z\d]*\s+[^>]+?>#msi', function ($ms) use (&$aid, &$oldTags) {
					$r             = "<!--a-id@{$aid}-->";
					$oldTags[ $r ] = $ms[0];
					$aid++;

					return $r;
				}, $content);

				$count = App::icfgn('tag_count@cms', 0);
				if (!$count) {
					$count = -1;
				}
				//3. 替换内链
				foreach ($tags as $n => $t) {
					$url           = App::base($t['url']);
					$r             = "<!--a-id@{$aid}-->";
					$oldTags[ $r ] = '<a href="' . $url . '" title="' . $t ['title'] . '">' . $t ['tag'] . '</a>';
					$aid++;
					$content = preg_replace('`' . preg_quote($t['tag'], '`') . '`u', $r, $content, $count);
				}
				//4. 还原
				if ($oldTags) {
					$rs      = array_keys($oldTags);
					$ps      = array_values($oldTags);
					$content = str_replace($rs, $ps, $content);
				}
			}
		}

		return $content;
	}

	/**
	 * 新标签.
	 *
	 * @param array      $data
	 * @param string|int $uid
	 *
	 * @return bool
	 */
	public function newTag($data, $uid) {
		$data['create_time'] = $data['update_time'] = time();
		$data['create_uid']  = $data['update_uid'] = intval($uid);

		try {
			return $this->insert($data);
		} catch (\Exception $e) {
			unset($data['create_time'], $data['create_uid']);

			return $this->update($data, ['tag' => $data['tag']]);
		}
	}

	/**
	 * 更新标签
	 *
	 * @param array $data
	 * @param int   $uid
	 *
	 * @return bool
	 */
	public function updateTag($data, $uid = 0) {
		$data['update_time'] = time();
		$data['update_uid']  = intval($uid);

		return $this->update($data, $data['id']);
	}

	/**
	 * 删除标签.
	 *
	 * @param array $ids
	 *
	 * @return bool
	 */
	public function deleteTags($ids) {
		return $this->delete(['id IN' => $ids]);
	}

}
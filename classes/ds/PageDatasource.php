<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\classes\ds;

use cms\classes\model\CmsPage;
use wulaphp\app\App;
use wulaphp\db\sql\Condition;
use wulaphp\form\FormTable;
use wulaphp\io\Response;
use wulaphp\mvc\model\CtsData;
use wulaphp\mvc\model\CtsDataSource;

class PageDatasource extends CtsDataSource {

	public function getName() {
		return '通用页面';
	}

	protected function getData($con, $db, $pageInfo, $tplvar) {
		try {
			$db    = App::db();
			$table = new CmsPage();
			$table->alias('CP');
			//查询
			$query = $table->select('CP.url,CP.id,CM.name AS model_name,CM.refid AS model_refid,CH.title2 AS channel_name,CHP.url AS channel_url,CPF.*');
			$query->join('{cms_page_field} AS CPF', 'CPF.page_id = CP.id');
			$query->join('{cms_model} AS CM', 'CPF.model = CM.id');
			$query->join('{cms_page_field} AS CH', 'CPF.channel = CH.page_id');
			$query->join('{cms_page} AS CHP', 'CPF.channel = CHP.id');
			//必须是已经发布的
			$id                  = aryget('id', $con);
			$where               = new Condition();
			$where['CPF.status'] = 1;
			//页面编号
			if ($id) {
				$where['CPF.page_id IN'] = safe_ids2($id);
			}
			//内容模型
			$model    = aryget('model', $con);
			$modelSet = false;
			if ($model) {
				$mid = $db->queryOne('select id from {cms_model} where refid = %s limit 0,1', $model);
				if ($mid && $mid['id']) {
					fire('cts\alter' . ucfirst($model) . 'Query', $query);
					$where['CPF.model'] = $mid['id'];
					$modelSet           = true;
				}
			}
			if (!$modelSet) {
				$where['CPF.model >'] = 3;
			}
			//栏目
			$sub  = aryget('sub', $con);
			$path = aryget('path', $con);
			if ($path) {
				if ($sub) {
					$where['CPF.path LIKE'] = $path . '%';
				} else {
					$where['CPF.path'] = $path;
				}
			}
			//标签
			$tags = aryget('tags', $con);
			if ($tags) {
				$tags = preg_split('#[,\s，-]#', trim($tags, ','));
				if ($tags) {
					$te         = $db->select('CPT.id')->from('{cms_page_tag} AS CPT')->where([
						'CPT.page_id' => imv('CPF.page_id'),
						'CPT.tag IN'  => $tags
					]);
					$where['@'] = $te;
				}
			}
			//属性
			$flags = aryget('flags', $con);
			if ($flags) {
				$flags = preg_split('#[,\s，-]#', trim($flags, ','));
				if ($flags) {
					$fe         = $db->select('CPFL.id')->from('{cms_page_flag} AS CPFL')->where([
						'CPFL.page_id' => imv('CPF.page_id'),
						'CPFL.flag IN' => $flags
					]);
					$where['@'] = $fe;
				}
			}
			//排序
			$sort = aryget('sort', $con);
			if ($sort && $sort != 'n') {
				$order = aryget('order', $con, 'a');
				$sort  = explode(',', $sort);
				$sort  = 'CPF.' . implode('CPF.', $sort);
				$query->sort($sort, $order);
			}
			//分页
			$limit = aryget('limit', $con);
			if (!$limit) {
				$limit = '0,100';
			}
			$limit = explode(',', $limit);
			if (isset($limit[1])) {
				$page  = intval($limit[0]);
				$limit = intval($limit[1]);

			} else {
				$limit = intval($limit[0]);
				$page  = $pageInfo ? $pageInfo->page - 1 : 0;
			}
			$page = max(0, $page * $limit);
			$query->limit($page, $limit);
			$data = $query->where($where)->toArray();
			if ($data) {
				if (aryget('page', $con)) {
					$total = $query->total('CPF.page_id');
				} else {
					$total = 0;
				}

				return new CtsData($data, $total);
			} else if (aryget('r404', $con)) {
				Response::respond(404);
			}
		} catch (\Exception $e) {

		}

		return new CtsData([], 0);
	}

	public function getVarName() {
		return 'page';
	}

	public function getCondForm() {
		return new PageDatasourceForm(true);
	}

	public function getCols() {
		return [
			'id'           => 'ID',
			'model_name'   => '模型',
			'channel_name' => '栏目',
			'title'       => '标题',
			'title2'        => '副标题'
		];
	}
}

class PageDatasourceForm extends FormTable {
	public $table = null;
	/**
	 * ID(多个用','分隔)
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 1,col-xs-12
	 */
	public $id;
	/**
	 * 栏目路径
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 2,col-xs-12
	 */
	public $path;
	/**
	 * 子栏目
	 * @var \backend\form\CheckboxField
	 * @type string
	 * @layout 2,col-xs-12
	 */
	public $sub;
	/**
	 * 模型
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 3,col-xs-12
	 */
	public $model;
	/**
	 * 标签(多个用','分隔)
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 4,col-xs-12
	 */
	public $tags;
	/**
	 * 属性(多个用','分隔)
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 5,col-xs-12
	 */
	public $flags;

	/**
	 * 排序方式
	 * @var \backend\form\SelectField
	 * @type string
	 * @layout 10,col-xs-8
	 * @see    param
	 * @data   n=无&id=ID&cmts=评论次数&view=查看次数&dig=顶&dig1=踩&publish_time=发布时间&create_time=创建时间&update_time=更新时间
	 */
	public $sort;
	/**
	 * 排序
	 * @var \backend\form\SelectField
	 * @type string
	 * @layout 10,col-xs-4
	 * @see    param
	 * @data   a=升序&d=倒序
	 */
	public $order;
	/**
	 * 分页(格式: [<起始位置,条数>|<条数>])
	 * @var \backend\form\TextField
	 * @type string
	 * @layout 96,col-xs-12
	 */
	public $limit = '10';
	/**
	 * 计算分页信息
	 * @var \backend\form\CheckboxField
	 * @type string
	 * @layout 96,col-xs-12
	 */
	public $page;
	/**
	 * 分页超出返回404
	 * @var \backend\form\CheckboxField
	 * @type string
	 * @layout 96,col-xs-12
	 */
	public $r404;
}
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

use cms\block\model\CmsBlockItem;
use wulaphp\form\FormTable;
use wulaphp\mvc\model\CtsData;
use wulaphp\mvc\model\CtsDataSource;

class BlockDatasource extends CtsDataSource {
	public function getName() {
		return '页面区块';
	}

	protected function getData($con, $db, $pageInfo, $tplvar) {
		$block = aryget('block', $con);
		if (!$block) {
			return new CtsData();
		}
		$itemM = new CmsBlockItem();
		//区块
		$where['pn'] = $block;
		$order       = aryget('order', $con, 'a');
		$q           = $itemM->select();
		//排序
		$q->sort(aryget('sort', $con, 'sort'), $order);
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
		$q->limit($page, $limit)->where($where);
		$data = $q->toArray();
		if ($data) {
			if (aryget('page', $con)) {
				$total = $q->total('id');
			} else {
				$total = 0;
			}

			return new CtsData($data, $total);
		}

		return new CtsData();
	}

	public function getCondForm() {
		return new BlockDatasourceForm(true);
	}

	public function getVarName() {
		return 'item';
	}

	public function getCols() {
		return ['id' => 'ID', 'title' => '标题', 'url' => 'URL', 'image' => '图1', 'num' => '数值'];
	}
}

class BlockDatasourceForm extends FormTable {
	public $table = null;

	/**
	 * 区块
	 * @var \backend\form\ComboxField
	 * @type string
	 * @layout 5,col-xs-12
	 * @option {"url":"cms/block/item/q","allowClear":1,"mnl":0}
	 */
	public $block;
	/**
	 * 排序方式
	 * @var \backend\form\SelectField
	 * @type string
	 * @layout 10,col-xs-8
	 * @see    param
	 * @data   sort=指定排序&create_time=创建时间&update_time=修改时间&id=ID&num=数值1&num1=数值2&num2=数值3
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
}
<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace cms\cts\controllers;

use backend\classes\IFramePageController;
use backend\form\BootstrapFormRender;

/**
 * 模板调用
 *
 * @role    开发人员
 * @package cms\cts\controllers
 */
class IndexController extends IFramePageController {
	public function index() {
		$data = [];
		$ds   = get_cts_datasource();
		foreach ($ds as $d => $dsi) {
			$ddd['id']     = $d;
			$ddd['name']   = $dsi->getName();
			$data['dss'][] = $ddd;
		}

		return $this->render($data);
	}

	public function preview($ds) {
		if (!$ds) {
			return 'no datasource found';
		}
		$dss = get_cts_datasource();
		if (!isset($dss[ $ds ])) {
			return 'no datasource named ' . $ds;
		}
		$dsi  = $dss[ $ds ];
		$form = $dsi->getCondForm();
		$data = ['ds' => $ds];
		if ($form) {
			$data['form'] = BootstrapFormRender::v($form);
		}
		$data['cols'] = $dsi->getCols();

		return view($data);
	}

	public function data($ds) {
		if (!$ds) {
			return '<tbody></tbody>';
		}
		$dss = get_cts_datasource();
		if (!isset($dss[ $ds ])) {
			return '<tbody></tbody>';
		}
		$dsi  = $dss[ $ds ];
		$form = $dsi->getCondForm();

		$data['cols']    = $dsi->getCols();
		$data['colSpan'] = count($data['cols']);
		if ($form) {
			$con = $form->inflate();
		} else {
			$con = [];
		}
		$rows         = get_cts_from_datasource($ds, $con);
		$data['rows'] = $rows->toArray();

		$cons = ['{cts', 'var=' . $dsi->getVarName(), 'from=' . $ds];
		foreach ($con as $key => $c) {
			$cons[] = $key . '="' . $c . '"';
		}
		$cons[]       = '}{/cts}';
		$data['code'] = implode(' ', $cons);

		return view($data);
	}
}
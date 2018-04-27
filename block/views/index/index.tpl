<section class="hbox stretch wulaui">
    <aside class="aside aside-lg b-r">
        <section class="vbox">
            <header class="header bg-light b-b clearfix p-l-xs p-r-xs">
                <div class="btn-group">
                    <button class="btn btn-default" data-toggle="dropdown" style="min-width: 210px;max-width: 210px;">
                        <span id="page-name">全部</span>
                    </button>
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" data-load="{'cms/block/pages'|app}" data-lazy
                        id="pages" data-loading-target="#pages">
                        {include './pages.tpl'}
                    </ul>
                </div>
            </header>
            <section class="scrollable w-f" id="blocks" data-load="{'cms/block/blocks'|app}" data-lazy
                     data-loading-target="#blocks">
                {include './blocks.tpl'}
            </section>
            <footer class="footer bg-light b-t">
                <a class="btn btn-success btn-sm pull-right edit-dialog" data-ajax="dialog"
                   href="{'cms/block/edit'|app}" data-title="新的区块">
                    <i class="fa fa-plus"></i> 新区块
                </a>
            </footer>
        </section>
    </aside>
    <section>
        <section class="vbox">
            <header class="header bg-light b-b clearfix">
                <div class="row m-t-sm">
                    <div class="col-xs-9 hidden-xs m-b-xs">
                        <button class="btn btn-default">
                            <i class="fa fa-list"></i>
                            <span id="m-name">未选择区块</span>
                        </button>
                        <a class="act btn btn-sm btn-success edit-item hidden" data-ajax="dialog" data-title="新的条目">
                            <i class="fa fa-plus"></i> 添加条目
                        </a>
                        <a href="{'cms/block/item/del'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                           data-confirm="你真的要删除这些条目吗？" data-warn="请选择要删除的条目" class="btn btn-danger btn-sm"><i
                                    class="fa fa-trash"></i> 删除</a>
                    </div>
                    <div class="col-xs-3 m-b-xs">
                        <form class="form-inline" data-table-form="#table">
                            <div class="input-group input-group-sm">
                                <input type="hidden" name="blockid" value="" id="blockid"/>
                                <input type="text" name="q" class="input-sm form-control" placeholder="{'Search'|t}"
                                       data-toggle="tooltip" data-placement="bottom"
                                       title="<p class='text-left'>可用查询:<br/>1.标题，如'标题' % 你好%<br/>2.副标题<br/>3.作者,来源,标签,属性<br/>4.url</p>"/>
                                <span class="input-group-btn">
                                    <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </header>
            <section class="w-f">
                <div class="table-responsive">
                    <table id="table" data-auto data-table="{'cms/block/item/data'|app}" data-sort="id,d"
                           style="min-width: 800px">
                        <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" class="grp"/>
                            </th>
                            <th width="180" data-sort="pn,a">区块</th>
                            <th>标题</th>
                            <th width="120">副标题</th>
                            <th width="120">图片</th>
                            <th>自定义数值</th>
                            <th width="60" data-sort="sort,a">排序</th>
                            <th width="70"></th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </section>
            <footer class="footer bg-light b-t">
                <div data-table-pager="#table"></div>
            </footer>
        </section>
    </section>
</section>
<script type="text/javascript">
	layui.use(['jquery', 'bootstrap', 'wulaui', 'clipboard'], function ($, b, ui, cb) {
		$('body').on('click', '.filter-page', function () {
			var pn = $(this).data('name');
			$('#page-name').text(pn);
			//TODO: 1. 刷新区块列表，2，刷新列表
			$('#blocks').data('load', ui.app('cms/block/blocks/' + pn)).reload();
		}).on('click', '.filter-item', function () {
			var id = $(this).parent().data('id');
			$('#m-name').html($(this).text());
			$('#blockid').val(id);
			$('#btn-do-search').click();
			$('.act').removeClass('hidden')
				.attr('href', ui.app('cms/block/item/edit/0/' + id))
				.attr('title', '添加『' + $(this).text() + '』条目');
		}).on('ajax.success', '.act-del', function () {
			$('#blocks').reload(null, true);
			$('#m-name').html('未选择区块');
			$('.act').addClass('hidden');
			$('#blockid').val('0');
			$('#btn-do-search').click();
		}).on('before.dialog', '.edit-dialog', function (e) {
			e.options.zIndex = 9990;
			e.options.area   = '400px,300px';
			e.options.btn    = ['保存', '取消'];
			e.options.yes    = function () {
				$('#edit-form').on('ajax.success', function () {
					var pagen = $('#page').val();
					$('#page-name').text(pagen);
					$('#blocks').reload(ui.app('cms/block/blocks/' + pagen), true);
					$('#pages').reload(null, true);
					layer.closeAll()
				}).submit();
				return false;
			};
		}).on('before.dialog', '.edit-item', function (e) {
			e.options.zIndex = 9990;
			e.options.area   = '600px,500px';
			e.options.btn    = ['保存', '取消'];
			e.options.yes    = function () {
				$('#edit-item').on('ajax.success', function () {
					layer.closeAll()
				}).submit();
				return false;
			};
		});
	});
</script>
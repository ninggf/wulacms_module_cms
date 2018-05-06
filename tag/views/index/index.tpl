<section class="vbox wulaui">
    <header class="header bg-light b-b clearfix">
        <div class="row m-t-sm">
            <div class="col-xs-9 hidden-xs m-b-xs">
                {if $canEdit}
                    <a href="{'cms/tag/edit'|app}" class="act btn btn-sm btn-success edit-item" data-ajax="dialog"
                       data-title="新的标签">
                        <i class="fa fa-plus"></i> 添加标签
                    </a>
                {/if}
                {if $canDict}
                <a href="{'cms/tag/dict'|app}" data-ajax data-confirm="如果标签较多，生成词典可能需要几分钟时间，请耐心等待!"
                   class="btn btn-primary btn-sm" data-loading data-timeout="600000"><i class="fa fa-book"></i> 生成词典
                    </a>{/if}
                {if $canDel}
                <a href="{'cms/tag/del'|app}" data-ajax data-grp="#table tbody input.grp:checked"
                   data-confirm="你真的要删除这些标签吗？" data-warn="请选择要删除的标签" class="btn btn-danger btn-sm"><i
                            class="fa fa-trash"></i> 删除</a>{/if}
            </div>
            <div class="col-xs-3 m-b-xs text-right">
                <form class="form-inline" data-table-form="#table">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="input-sm form-control" placeholder="标签"/>
                        <p class="input-group-btn">
                            <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </header>
    <section class="w-f scrollable">
        <div class="table-responsive">
            <table id="table" data-auto data-table="{'cms/tag/data'|app}" data-sort="sort,a" style="min-width: 800px">
                <thead>
                <tr>
                    <th width="30">
                        <input type="checkbox" class="grp" title=""/>
                    </th>
                    <th width="150" data-sort="tag,a">标签</th>
                    <th width="200">标题</th>
                    <th>链接(URL)</th>
                    <th width="130">最后更新</th>
                    <th width="80" data-sort="sort,a">权重</th>
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
<script type="text/javascript">
	layui.use(['jquery', 'wulaui'], function ($, ui) {
		$('body').on('before.dialog', '.edit-item', function (e) {
			e.options.zIndex = 9990;
			e.options.area   = '500px,260px';
			e.options.btn    = ['保存', '取消'];
			e.options.yes    = function () {
				$('#edit-form').on('ajax.success', function () {
					layer.closeAll()
				}).submit();
				return false;
			};
		}).on('change', 'input.sort', function () {
			var sort = $(this).val();
			if (!/^\d+$/.test(sort)) {
				$(this).val(999);
				ui.toast.warning(sort + '不是一个有效的排序值');
			} else {
				$.get(ui.app('cms/tag/csort'), {
					id  : $(this).data('id'),
					sort: sort
				}, function () {

				}, 'json');
			}
		});
	});
</script>
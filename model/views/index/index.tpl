<div class="vbox wulaui hidden" id="workspace">
    <header class="bg-light header b-b clearfix">
        <div class="row m-t-sm">
            <div class="col-sm-6 m-b-xs">
                <a href="{'cms/model/edit'|app}" class="btn btn-sm btn-success edit" data-ajax="dialog"
                   data-area="400px,300px" data-title="新的模型">
                    <i class="fa fa-plus"></i> 添加模型
                </a>
            </div>
            <div class="col-sm-6 m-b-xs text-right">
                <form data-table-form="#table" class="form-inline">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="input-sm form-control" placeholder="{'搜索'|t}"/>
                        <span class="input-group-btn">
                            <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </header>
    <section class="w-f bg-white">
        <div class="table-responsive">
            <table id="table" data-auto data-table="{'cms/model/data'|app}" data-sort="refid,a"
                   style="min-width: 600px">
                <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" class="grp"/>
                    </th>
                    <th width="150">模型名</th>
                    <th data-sort="refid,a" width="100">标识</th>
                    <th width="100">自定义字段</th>
                    <th>可用属性</th>
                    <th width="100"></th>
                </tr>
                </thead>
            </table>
        </div>
    </section>
    <footer class="footer b-t">
        <div data-table-pager="#table"></div>
    </footer>
</div>
<script type="text/javascript">
	layui.use(['jquery', 'layer', 'wulaui'], function ($, layer) {
		$('#workspace').removeClass('hidden');
		$('body').on('before.dialog', '.edit', function (e) { // 增加编辑用户
			e.options.btn = ['保存', '取消'];
			e.options.yes = function () {
				$('#edit-form').on('ajax.success', function () {
					layer.closeAll()
				}).submit();
				return false;
			};
		});
	})
</script>
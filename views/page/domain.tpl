<div class="hbox wulaui stretch">
    <section id="core-users-workset">
        <header class="bg-light header b-b clearfix">
            <div class="row m-t-sm">
                <div class="col-sm-6 m-b-xs">
                    <a href="{'cms/page/edit'|app}" class="btn btn-sm btn-success edit-admin" data-ajax="dialog"
                       data-area="500px,400px" data-title="新的域名">
                        <i class="fa fa-plus"></i> 添加域名
                    </a>
                </div>
                <div class="col-sm-6 m-b-xs text-right">
                    <form data-table-form="#core-admin-table" class="form-inline">
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
                <table id="core-admin-table" data-auto data-table="{'cms/page/domain_data'|app}" data-sort="id,d"
                       style="min-width: 800px">
                    <thead>
                    <tr>
                        <th width="70">
                            <input type="checkbox" class="grp"/>
                        </th>
                        <th width="100" data-sort="id,d">ID</th>
                        <th width="200" data-sort="domain,a">域名</th>
                        <th width="100">是否默认</th>
                        <th width="100">是否https</th>
                        <th width="100">模板目录</th>
                        <th></th>
                    </tr>
                    </thead>
                </table>
            </div>
        </section>
        <footer class="footer b-t">
            <div data-table-pager="#core-admin-table"></div>
        </footer>
    </section>

</div>
{literal}
    <script>
		layui.use(['jquery', 'layer', 'wulaui'], function ($, layer) {
			//对话框处理
			$('#core-users-workset').on('before.dialog', '.edit-admin', function (e) { // 增加编辑用户
				e.options.btn = ['保存', '取消'];
				e.options.yes = function () {
					$('#core-admin-form').on('ajax.success', function () {
						layer.closeAll()
					}).submit();
					return false;
				};
			});

		})
		;
    </script>
{/literal}

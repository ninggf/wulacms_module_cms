<div class="vbox wulaui" id="core-users-workset">
    <header class="bg-light header b-b clearfix">
        <div class="row m-t-sm">
            <div class="col-sm-6 m-b-xs">
                <a href="{'cms/domain/edit'|app}" class="btn btn-sm btn-success edit-admin" data-ajax="dialog"
                   data-area="700px,500px" data-title="新的域名">
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
            <table id="core-admin-table" data-auto data-table="{'cms/domain/data'|app}" data-sort="is_default,d"
                   style="min-width: 800px">
                <thead>
                <tr>
                    <th width="20">
                        <input type="checkbox" class="grp"/>
                    </th>
                    <th width="50" data-sort="id,d">ID</th>
                    <th width="150">网站名称</th>
                    <th data-sort="domain,a">域名</th>
                    <th width="100">主页模板</th>
                    <th width="60">缓存</th>
                    <th width="60">离线</th>
                    <th width="60" data-sort="is_default,d">默认</th>
                    <th width="60">https</th>
                    <th width="80">模板目录</th>
                    <th width="80"></th>
                </tr>
                </thead>
            </table>
        </div>
    </section>
    <footer class="footer b-t">
        <div data-table-pager="#core-admin-table"></div>
    </footer>
</div>
<script>
	layui.use(['jquery', 'layer', 'wulaui'], function ($, layer) {
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
</script>
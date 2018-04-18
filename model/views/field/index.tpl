<section class="hbox stretch wulaui">
    <aside class="aside aside-lg">
        <section class="vbox">
            <header class="header bg-light lt clearfix b-b">
                <button class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#fields-wrap">
                    <i class="fa fa-reorder"></i>
                </button>
                <p class="h4">字段列表</p>
            </header>
            <section class="w-f scrollable hidden-xs m-t-xs" id="fields-wrap">
                <div id="field-list" data-load="{'cms/model/field/fields'|app}/{$model}" data-auto
                     data-loading="#field-list"></div>
            </section>
            <footer class="footer bg-light b-t">
                <a class="btn btn-success btn-sm pull-right edit" data-ajax="dialog"
                   href="{'cms/model/field/edit'|app}/0/{$model}" data-area="700px,500px" data-title="新的字段">
                    <i class="fa fa-plus"></i> 新字段
                </a>
            </footer>
        </section>
    </aside>
    <section class="b-l">
        <section class="vbox">
            <header class="header bg-light lt b-b clearfix">
                <p class="h4">表单预览</p>
            </header>
            <section class="scrollable">
                <div id="form-preview" data-load="{'cms/model/field/preview'|app}/{$model}" data-auto
                     data-loading="#form-preview"></div>
            </section>
        </section>
    </section>
</section>
<div class="hidden">
    <p id="field-pop-menu" class="text-lg">
        <a data-ajax="dialog" href="{"cms/model/field/edit"|app}" data-title="编辑字段" data-area="700px,500px"
           class="text-warning edit" title="编辑"><i class="fa fa-pencil-square-o"></i></a>
        <a data-ajax="dialog" href="{"cms/model/field/cfg"|app}" class="text-primary cfg" data-title="字段配置"
           data-area="600px,400px">
            <i class="fa fa-gear"></i>
        </a>
        <a data-ajax href="{"cms/model/field/del"|app}" class="text-danger" title="删除" data-confirm="字段删除后将不可恢复!">
            <i class="fa fa-trash-o"></i>
        </a>
    </p>
</div>
<script type="text/javascript">
	layui.use(['jquery', 'layer', 'wulaui'], function ($, layer) {
		$('body').on('before.dialog', '.edit', function (e) { // 增加编辑用户
			e.options.btn = ['保存', '取消'];
			e.options.yes = function () {
				$('#edit-form').on('ajax.success', function () {
					$('#form-preview').reload();
					layer.closeAll();
				}).submit();
				return false;
			};
		}).on('before.dialog', '.cfg', function (e) { // 增加编辑用户
			e.options.btn = ['保存', '取消'];
			e.options.yes = function () {
				$('#cfg-form').on('ajax.success', function () {
					layer.closeAll();
				}).submit();
				return false;
			};
		});
		$('#field-pop-menu').on('ajax.success', '.text-danger', function () {
			$('#form-preview').reload();
		});
	});
</script>
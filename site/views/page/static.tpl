<div class="wulaui max1000">
    <section class="p-md m-b-md">
        <form action="{'cms/site/page/save'|app}" id="PageForm" name="PageForm" method="post" class="form" role="form"
              data-validate="{$validate_rules|escape}" data-ajax data-loading>
            <input type="hidden" name="model" value="{$cmodel.id}"/>
            <input type="hidden" name="channel" value="{$cchannel.chid}"/>
            <div class="form-group">
                <p class="form-control-static"><strong>栏目：</strong>{$cchannel.name},
                    <strong>路径：</strong>{$cchannel.path}</p>
            </div>
            {if $form}
                {$form|render}
            {/if}
            <div style="position: fixed;bottom:-10px;left:50%;margin-left: -70px;" class="form-group">
                <div class="btn-group btn-group-lg">
                    <button type="reset" id="reset-btn" class="btn btn-default">重置</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>
        </form>
    </section>
</div>
<script type="text/javascript">
	layui.use(['jquery', 'layer', 'wulaui'], function ($, layer) {
		$('#PageForm').on('ajax.success', function (e, data) {
			if (data && data.code === 200 && data.args) {
				if (top.jqTabmenu) {
					if (data.args.isNew) {
						layer.confirm('添加成功，是否继续新增?', {
							icon : 1,
							title: '恭喜'
						}, function (idx) {
							location.reload(true);
							layer.close(idx);
						}, function (idx) {
							top.jqTabmenu.close(location);
							layer.close(idx);
						});
					} else {
						layer.confirm('修改成功，是否继续编辑?', {
							icon : 1,
							title: '恭喜'
						}, function (idx) {
							layer.close(idx);
						}, function (idx) {
							top.jqTabmenu.close(location);
							layer.close(idx);
						});
					}
				} else {
					location.reload(true);
				}
			}
		});
	});
</script>
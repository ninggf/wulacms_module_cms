<div class="wulaui max1000">
    <section class="p-md m-b-md">
        <form action="{'cms/site/page/save'|app}" id="PageForm" name="PageForm" method="post" class="form" role="form"
              data-validate="{$validate_rules|escape}" data-ajax data-loading>
            <input type="hidden" name="model" value="1"/>
            <div class="form-group">
                <p class="form-control-static"><strong>上级栏目：</strong>{$cchannel.name},
                    <strong>路径：</strong>{$cchannel.path}</p>
            </div>
            {if $form}
                {$form|render}
            {/if}
            {if $dform}
                {$dform|render}
            {/if}
            {if $models}
                <div class="form-group">
                    <label>内容模型配置</label>
                    <div>
                        <table class="table table-condensed m-b-none">
                            <tr>
                                <th width="120">模型名称</th>
                                <th width="60">关联</th>
                                <th width="60">启用</th>
                                <th>URL生成规则</th>
                                <th width="150">模板文件</th>
                                <th width="150">列表模板文件</th>
                            </tr>
                            {foreach $models as $model}
                                <tr>
                                    <td>
                                        {$model.name}
                                        <input type="hidden" name="bm[{$model.mid}][id]" value="{$model.id}">
                                    </td>
                                    <td>
                                        <input type="checkbox" name="bm[{$model.mid}][b]"
                                               {if $model.model}checked="checked"{/if}/>
                                    </td>
                                    <td>
                                        <input type="checkbox" name="bm[{$model.mid}][e]"
                                               {if $model.enabled}checked="checked"{/if}/>
                                    </td>
                                    <td>
                                        <input style="width: 100%" class="form-control p-xs" type="text"
                                               name="bm[{$model.mid}][url]" value="{$model.url_pattern|escape}">
                                    </td>
                                    <td>
                                        <input style="width: 100%" class="form-control p-xs" type="text"
                                               name="bm[{$model.mid}][tpl]" value="{$model.template_file|escape}">
                                    </td>
                                    <td>
                                        <input style="width: 100%" class="form-control p-xs" type="text"
                                               name="bm[{$model.mid}][tpl2]" value="{$model.template_file2|escape}">
                                    </td>
                                </tr>
                            {/foreach}
                        </table>
                    </div>
                </div>
            {/if}
            <div class="form-group">
                <div class="col-sm-4 col-sm-offset-4">
                    <button type="reset" id="reset-btn" class="btn btn-default">重置</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>
        </form>
    </section>
</div>
<script type="text/javascript">
	layui.use(['jquery', 'layer', 'wulaui'], function ($, layer, wulaui) {
		$('#PageForm').on('ajax.success', function (e, data) {
			if (data && data.code === 200 && data.args) {
				if (top.jqTabmenu) {
					top.jqTabmenu.reload(wulaui.app('cms/site'));
					if (data.args.isNew) {
						layer.confirm('栏目添加成功，是否继续新增?', {
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
						layer.confirm('栏目修改成功，是否继续编辑?', {
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
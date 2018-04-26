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
                                        <input style="width: 100%" class="form-control p-xs urlp" type="text"
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
            <div style="position: fixed;bottom:-10px;left:50%;margin-left: -70px;" class="form-group">
                <div class="btn-group btn-group-lg">
                    <button type="reset" id="reset-btn" class="btn btn-default">重置</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </div>
        </form>
    </section>
    <div id="urlp" class="hidden">
        {literal}
            {Y}、{M}、{D} 年月日
            <br/>
            {aid}、{cc} 文章ID、36进制的文章ID
            <br/>
            {path}、{rpath} 全路径、退一格路径
            <br/>
            {tid} 栏目ID
            <br/>
            {model}模型
            <br/>
            {title} 标题
            <br/>
            {py} 标题的拼音
        {/literal}
    </div>
</div>
<script type="text/javascript">
	layui.use(['jquery', 'layer', 'bootstrap', 'wulaui'], function ($, layer, bt, wulaui) {
		let imgUploaded = false;
		$('.urlp').popover({
			title    : '可用变量',
			content  : function () {
				return $('#urlp').html();
			},
			html     : true,
			placement: 'auto top',
			trigger  : 'focus'
		});
		$('#PageForm').on('ajax.before', function () {
			let uploader = $('[data-uploader]'), hasImg = uploader.length;
			//检查图片是否全部上传
			if (hasImg && !imgUploaded) {
				uploader.on('uploader.done', function () {
					hasImg--;
					if (!hasImg) {
						//全部上传完成后再提交
						imgUploaded = true;
						$('#PageForm').trigger('submit');
					}
				});
				uploader.each(function (i, up) {
					$(up).data('uploaderObj').start();
				});
				return false;
			}
		}).on('ajax.success', function (e, data) {
			imgUploaded = false;//再次保存时需要检查图片是否上传。
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
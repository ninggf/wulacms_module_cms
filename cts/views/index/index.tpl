<section class="hbox stretch wulaui">
    <aside class="aside aside-sm b-r">
        <section class="vbox">
            <header class="header bg-light b-b clearfix">
                <p class="h4">数据源</p>
            </header>
            <section class="scrollable">
                <ul class="nav nav-pills nav-stacked no-radius m-t-xs" id="ds-list">
                    {foreach $dss as $role}
                        <li data-rid="{$role.id}">
                            <a href="javascript:void(0);" class="role-li">{$role.name}</a>
                        </li>
                    {/foreach}
                </ul>
            </section>
        </section>
    </aside>
    <section>
        <section class="vbox">
            <header class="header bg-light b-b clearfix">
                <p class="h4">数据预览</p>
            </header>
            <section id="data-preview" data-load data-lazy data-loading-target="#data-preview"></section>
        </section>
    </section>
</section>

<script type="text/javascript">
	layui.use(['jquery', 'wulaui', 'clipboard'], function ($, wulaui, cp) {

		$('body').on('click', 'a.role-li', function () { //分角色查看用户
			var me = $(this), mp = me.closest('li'), rid = mp.data('rid'), group = me.closest('ul');
			if (mp.hasClass('active')) {
				return;
			}
			group.find('li').not(mp).removeClass('active');
			mp.addClass('active');

			$('#data-preview').data('load', "{'cms/cts/preview'|app}/" + rid).reload();
			return false;
		}).on('click', '.copy-cts', function () {
			var code = $('#cts-code').text();
			cp.copy({
				"text/plain": code
			}).then(function () {
				wulaui.toast.success('代码已复制');
			}, function () {
				wulaui.toast.error('代码无法复制,请手工复制吧');
			});
		});
	});
</script>
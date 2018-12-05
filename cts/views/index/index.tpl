<section class="hbox stretch wulaui">
    <aside class="aside aside-md app-menu">
        <section class="vbox">
            <header class="header b-b clearfix">
                <p class="h4">数据源</p>
            </header>
            <section class="scrollable">
                <ul class="layui-nav" id="ds-list">
                    {foreach $dss as $role}
                        <li class="layui-nav-item" data-rid="{$role.id}">
                            <a href="javascript:void(0);" class="role-li">
                                <i class="layui-icon layui-icon-list"></i>
                                {$role.name}
                            </a>
                        </li>
                    {/foreach}
                </ul>
            </section>
        </section>
    </aside>
    <section>
        <section class="vbox">
            <header class="header bg-white b-b clearfix">
                <p class="h4">数据预览 - <em id="ds-name"></em></p>
            </header>
            <section id="data-preview" data-load data-lazy data-loading-target="#data-preview"></section>
        </section>
    </section>
</section>

<script type="text/javascript">
    layui.use(['jquery', 'wulaui', 'clipboard'], function ($, wulaui, cp) {
        $('body').on('click', 'a.role-li', function () { //分角色查看用户
            var me = $(this), mp = me.closest('li'), rid = mp.data('rid'), group = me.closest('ul');
            if (mp.hasClass('layui-this')) {
                return;
            }
            group.find('li').not(mp).removeClass('layui-this');
            mp.addClass('layui-this');
            $('#ds-name').html(mp.text());
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
        $('#ds-list li:first a').click();
    });
</script>
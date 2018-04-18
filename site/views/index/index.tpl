<section class="hbox stretch wulaui hidden" id="cms-site-page">
    <aside class="aside aside-md b-r">
        <section class="vbox">
            <header class="header bg-light lt b-b">
                <button class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#channel-wrap">
                    <i class="fa fa-reorder"></i>
                </button>
                <div class="btn-group hidden-xs">
                    <button class="btn btn-default" data-toggle="dropdown" style="min-width: 160px;max-width: 170px;"
                            id="reload-ch">
                        <i class="fa fa-cloud-upload text-success"></i>&nbsp;<span id="ch-name">我的网站</span>
                    </button>
                    <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right" id="ch-status">
                        <li>
                            <a href="javascript:" data-status="1">
                                <i class="fa fa-cloud-upload text-success"></i> 已发布
                            </a>
                        </li>
                        <li>
                            <a href="javascript:" data-status="0">
                                <i class="fa fa-archive text-info"></i> 草稿箱
                            </a>
                        </li>
                        {if $canApprove}
                            <li>
                                <a href="javascript:" data-status="3">
                                    <i class="fa fa-check-square-o text-warning"></i> 待审核
                                </a>
                            </li>
                        {/if}
                        {if $canPublish}
                            <li>
                                <a href="javascript:" data-status="4">
                                    <i class="fa fa-cloud-upload text-primary"></i> 待发布
                                </a>
                            </li>
                        {/if}
                        <li>
                            <a href="javascript:" data-status="2">
                                <i class="fa fa-recycle text-danger"></i> 回收站
                            </a>
                        </li>
                    </ul>
                </div>
            </header>
            <section class="hidden-xs scrollable m-t-xs {if $canMgCh}w-f{/if}" id="channel-wrap">
                <div class="ztree m-l-n-xs" data-ztree="{"cms/site/channel-node"|app}" id="channel-tree"
                     data-lazy></div>
            </section>
            {if $canMgCh}
                <footer class="footer hidden-xs">
                    <div class="btn-group dropup pull-right">
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-gears"></i> 栏目管理
                        </button>
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span
                                    class="caret"></span></button>
                        <ul class="dropdown-menu" id="ch-btns">
                            <li class="act btn0 btn1">
                                <a id="edit-ch" data-tab="&#xe635;">
                                    <i class="fa fa-pencil-square-o text-primary"></i> 修改栏目
                                </a>
                            </li>
                            {if $canDelPage}
                                <li class="act btn0 btn1">
                                    <a id="delete-ch" data-ajax data-confirm="栏目删除后期内页面将不可访问，你确定要删除该栏目吗？">
                                        <i class="fa fa-trash-o text-danger"></i> 删除栏目
                                    </a>
                                </li>
                                <li class="act btn2">
                                    <a id="restore-ch" data-ajax data-confirm="你确定要还原该栏目吗?">
                                        <i class="fa fa-recycle text-primary"></i> 从回收站还原
                                    </a>
                                </li>
                            {/if}
                            {if $canPublish}
                                <li id="pub-ch" class="act btn0">
                                    <a data-ajax data-confirm="你确定要发布此栏目吗?">
                                        <i class="fa fa-cloud-upload text-success"></i> 发布栏目
                                    </a>
                                </li>
                            {/if}
                            {if $canClearCC}
                                <li class="act btn1 btn2">
                                    <a id="cc-ch" data-ajax data-confirm="你确定要清空栏目页面的缓存吗?">
                                        <i class="fa fa-eraser text-warning"></i> 清空缓存
                                    </a>
                                </li>
                                <li class="act btn1 btn2">
                                    <a id="cc-ch-all" data-ajax data-confirm="你确定要清空栏目(包括子栏目)的缓存吗?">
                                        <i class="fa fa-eraser text-warning"></i> 清空全部缓存
                                    </a>
                                </li>
                            {/if}
                            <li class="divider act btn0 btn1 btn2"></li>
                            <li class="showx act btn0 btn1">
                                <a id="add-sub-btn" data-tab="&#xe635;" href="{'cms/site/page/add/1'|app}"
                                   title="新增『顶级栏目』">
                                    <i class="fa fa-plus-square-o text-primary"></i> 添加子栏目</a>
                            </li>
                            <li class="showx act btn0 btn1 btn2">
                                <a id="add-sub-btn" data-tab="&#xe635;" href="{'cms/site/page/add/1'|app}"
                                   title="新增『顶级栏目』">
                                    <i class="fa fa-plus-square-o text-primary"></i> 添加顶级栏目</a>
                            </li>
                        </ul>
                    </div>
                </footer>
            {/if}
        </section>
    </aside>
    <section>
        <section class="vbox">
            <header class="header bg-light lt b-b clearfix">
                <div class="row m-t-sm">
                    <div class="col-xs-8 hidden-xs m-b-xs">
                        <div class="hidden" id="cms-toolbar">
                            <div class="btn-group">
                                <button class="btn btn-default">
                                    <i class="fa fa-eye"></i>
                                    <span id="m-name"></span>
                                </button>
                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span
                                            class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" id="view-content"></ul>
                            </div>
                            {if $canEditPage}
                                <div class="act btn-group btn1 btn0">
                                    <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                        <i class="fa fa-plus"></i> 添加
                                    </button>
                                    <button class="btn btn-success dropdown-toggle" data-toggle="dropdown"><span
                                                class="caret"></span></button>
                                    <ul class="dropdown-menu" id="new-content"></ul>
                                </div>
                            {/if}
                            {if $canPublish &&!true}
                                <div class="act btn-group btn1">
                                    <button class="btn btn-primary">
                                        <i class="fa fa-cloud-upload"></i>发布
                                    </button>
                                    <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown"><span
                                                class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#">Action</a></li>
                                        <li><a href="#">Another action</a></li>
                                        <li><a href="#">Something else here</a></li>
                                        <li class="divider"></li>
                                        <li><a href="#">Separated link</a></li>
                                    </ul>
                                </div>
                            {/if}
                            {if $canDelPage &&!true}
                                <div class="act btn-group btn1 btn2 btn0">
                                    <button class="btn btn-danger">
                                        <i class="fa fa-trash-o"></i>删除
                                    </button>
                                    <button class="btn btn-danger dropdown-toggle" data-toggle="dropdown"
                                            aria-expanded="false">
                                        <span class="caret"></span></button>
                                    <ul class="dropdown-menu">
                                        <li><a href="#">Action</a></li>
                                        <li><a href="#">Another action</a></li>
                                        <li><a href="#">Something else here</a></li>
                                        <li class="divider"></li>
                                        <li><a href="#">Separated link</a></li>
                                    </ul>
                                </div>
                            {/if}
                        </div>
                    </div>
                    <div class="col-xs-4 m-b-xs text-right">
                        <form data-table-form="#table" class="form-inline">
                            <input type="hidden" id="admin-role-id" name="rid" value=""/>
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" class="input-sm form-control" placeholder="{'Search'|t}"/>
                                <span class="input-group-btn">
                                    <button class="btn btn-sm btn-info" id="btn-do-search" type="submit">Go!</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
            </header>
            <section id="has-grid">
                <table class="layui-table" id="content-grid" lay-filter="grid"></table>
            </section>
        </section>
    </section>
    {foreach $modelToolbars as $tid=> $tb}
        <script type="text/html" id="{$tid}Toolbar">
            <div class="btn-group">
                {''|implode:$tb}
            </div>
        </script>
    {/foreach}
    <script type="text/javascript">
		layui.use(['jquery', 'ztree', 'wulaui', 'cms.main'], function ($, z, wulaui, cm) {
			cm.init({$canMgCh},{$modelGridCols|json_encode});
		});
    </script>
</section>
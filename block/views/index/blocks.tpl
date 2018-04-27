<div class="wulaui p-t-xs">
    {if $blocks}
        <ul class="nav nav-pills nav-stacked no-radius" data-pop-menu="#pop-menu">
            {foreach $blocks as $block}
                <li data-id="{$block.id}">
                    <a href="javascript:void(0);" class="filter-item">{$block.page}:{$block.name}</a>
                </li>
            {/foreach}
        </ul>
    {/if}
    <div class="hidden">
        <p id="pop-menu" class="text-lg">
            <a data-ajax="dialog" href="{"cms/block/edit"|app}" data-title="编辑区块" class="text-warning edit-dialog"
               title="编辑"><i class="fa fa-pencil-square-o"></i></a>

            <a data-ajax href="{"cms/block/del"|app}" class="text-danger act-del" title="删除" data-confirm="区块删除后将不可恢复!">
                <i class="fa fa-trash-o"></i>
            </a>
        </p>
    </div>
</div>
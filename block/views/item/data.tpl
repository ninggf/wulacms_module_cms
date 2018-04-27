<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.pn}</td>
        <td>
            <a href="{$row.url|url}" target="_blank">{$row.title}</a>
        </td>
        <td>{$row.title2}</td>
        <td>
            {if $row.image}
                <a href="{$row.image|media}" target="_blank">图1</a>
                &nbsp;
            {/if}
            {if $row.image1}
                <a href="{$row.image1|media}" target="_blank">图2</a>
                &nbsp;
            {/if}
            {if $row.image2}
                <a href="{$row.image2|media}" target="_blank">图3</a>
            {/if}
        </td>
        <td>{$row.num},{$row.num1},{$row.num2}</td>
        <td>{$row.sort}</td>
        <td>
            <div class="btn-group">
                <a href="{'cms/block/item/edit'|app}/{$row.id}/{$row.block_id}" title="编辑"
                   class="btn btn-xs btn-primary edit-item" data-ajax="dialog">
                    <i class="fa fa-pencil-square-o"></i>
                </a>
                <a href="{'cms/block/item/del'|app}/{$row.id}" class="btn btn-xs btn-danger" data-ajax
                   data-confirm="你真的要删除该条目吗?">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="8" class="text-center">区块里没条目</td>
    </tr>
{/foreach}
</tbody>
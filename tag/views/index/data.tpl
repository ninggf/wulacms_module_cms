<tbody total="{$total}">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" class="grp" value="{$row.id}"/>
        </td>
        <td>
            <a target="_blank" href="{$row.url|url}">{$row.tag}</a>
        </td>
        <td>
            {$row.title}
        </td>
        <td>
            {$row.url}
        </td>
        <td>
            {$row.update_time|date_format:'Y-m-d H:i'}
        </td>
        <td>
            <input data-id="{$row.id}" type="text" class="sort input-s-sm" style="width:100%" value="{$row.sort}"/>
        </td>
        <td>
            <div class="btn-group">
                {if $canEdit}
                <a href="{'cms/tag/edit'|app}/{$row.id}" title="编辑" class="btn btn-xs btn-primary edit-item"
                   data-ajax="dialog">
                        <i class="fa fa-pencil-square-o"></i>
                    </a>{/if}
                {if $canDel}
                <a href="{'cms/tag/del'|app}/{$row.id}" class="btn btn-xs btn-danger" data-ajax
                   data-confirm="你真的要删除该标签吗?">
                        <i class="fa fa-trash-o"></i>
                    </a>{/if}
            </div>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="7">无标签</td>
    </tr>
{/foreach}
</tbody>
<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.id}</td>
        <td>{$row.domain}</td>
        <td>{if $row.is_default==1}是{else}不是{/if}</td>
        <td>{if $row.is_https==1}是{else}不是{/if}</td>
        <td>{$row.theme}</td>
        <td class="text-right">
            <a href="{'cms/page/edit'|app}/{$row.id}" data-ajax="dialog" data-area="500px,400px"
               data-title="编辑『{$row.domain|escape}』" class="btn btn-xs edit-admin">
                <i class="layui-icon" style="font-size: 20px;color: #01AAED;">&#xe642;</i>
            </a>
            <a href="{'cms/page/del_domain'|app}/{$row.id}" data-confirm="你真的要删除?" data-ajax
               class="btn btn-xs edit-admin">
                <i class="layui-icon" style="font-size: 20px;color: #FF5722;">&#xe640;</i>
            </a>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="{'core.admin.table'|tablespan:5}" class="text-center">暂无相关数据!</td>
    </tr>
{/foreach}
</tbody>
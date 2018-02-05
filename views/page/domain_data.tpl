<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.id}</td>
        <td>{$row.domain}</td>
        <td>{$row.is_default}</td>
        <td>{$row.is_https}</td>
        <td>{$row.theme}</td>
        <td class="text-right">
            <a href="{'wechat/account/edit'|app}/{$row.id}" data-ajax="dialog" data-area="800px,500px"
               data-title="编辑『{$row.wx_name|escape}』" class="btn btn-xs edit-admin">
                <i class="layui-icon" style="font-size: 20px;color: #01AAED;">&#xe642;</i>
            </a>
            <a href="{'wechat/account/del'|app}/{$row.id}" data-confirm="你真的要删除?" data-ajax class="btn btn-xs edit-admin">
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
<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr>
        <td><input type="checkbox" value="{$row.id}" class="grp"/></td>
        <td>{$row.name}</td>
        <td>{$row.refid}</td>
        <td class="text-center">{$row.field_total}</td>
        <td>{$row.flags}</td>
        <td class="text-right">
            <div class="btn-group">
                <a href="{'cms/model/edit'|app}/{$row.id}" data-ajax="dialog" data-area="600px,350px"
                   data-title="编辑『{$row.name|escape}』" class="btn btn-xs btn-primary edit">
                    <i class="fa fa-pencil-square"></i>
                </a>
                <a href="{'cms/model/field'|app}/{$row.id}" class="btn btn-xs btn-warning" data-tab="&#xe61a;"
                   title="『{$row.name}』字段">
                    <i class="fa fa-list-ul"></i>
                </a>
                <a href="{'cms/model/del'|app}/{$row.id}" data-confirm="将删除与此模块相关的一切内容且不可恢复，你真的要删除?" data-ajax
                   class="btn btn-xs btn-danger">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="6" class="text-center">暂无相关数据!</td>
    </tr>
{/foreach}
</tbody>
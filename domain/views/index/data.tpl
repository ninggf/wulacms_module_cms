<tbody data-total="{$total}" class="wulaui">
{foreach $rows as $row}
    <tr rel="d-{$row.id}">
        <td></td>
        <td>
            <input type="checkbox" value="{$row.id}" class="grp"/>
        </td>
        <td>{$row.id}</td>
        <td>
            {if $row.is_https}
                <a href="https://{$row.domain}" target="_blank">{$row.name}</a>
            {else}
                <a href="http://{$row.domain}" target="_blank">{$row.name}</a>
            {/if}
        </td>
        <td>{$row.domain}</td>
        <td>{$row.tpl}</td>
        <td>{$row.expire}</td>
        <td>{if $row.offline==1}是{else}否{/if}</td>
        <td>{if $row.is_default==1}是{else}否{/if}</td>
        <td>{if $row.is_https==1}是{else}否{/if}</td>
        <td>{$row.theme}</td>
        <td class="text-right">
            <div class="btn-group">
                <a href="{'cms/domain/edit'|app}/{$row.id}" data-ajax="dialog" data-area="800px,600px"
                   data-title="编辑『{$row.domain|escape}』" class="btn btn-xs btn-primary edit-admin">
                    <i class="fa fa-pencil-square"></i>
                </a>
                <a href="{'cms/domain/cc'|app}/{$row.theme}" data-confirm="你真的要清空模板缓存吗?" data-ajax
                   class="btn btn-xs btn-warning">
                    <i class="fa fa-eraser"></i>
                </a>
                <a href="{'cms/domain/del'|app}/{$row.id}" data-confirm="你真的要删除?" data-ajax
                   class="btn btn-xs btn-danger">
                    <i class="fa fa-trash-o"></i>
                </a>
            </div>
        </td>
    </tr>
    <tr parent="d-{$row.id}">
        <td colspan="4"></td>
        <td colspan="8">
            <label class="label bg-success m-l-xs">{$row.domain}</label>
            {if $row.domains}{$row.domains|trim|replace:"\n":','}{/if}
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="12" class="text-center">无</td>
    </tr>
{/foreach}
</tbody>
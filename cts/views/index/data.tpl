<tbody>
{foreach $rows AS $row}
    <tr>
        <td>{$row@index+1}</td>
        {foreach $cols as $id=>$col}
            <td>{$row[$id]|escape}</td>
        {/foreach}
    </tr>
{/foreach}
<tr>
    <td>
        <button class="btn btn-xs btn-default copy-cts">
            <i class="fa fa-copy"></i>
        </button>
    </td>
    <td colspan="{$colSpan}" id="cts-code">{$code|escape}</td>
</tr>
{if $row}
    <tr>
        <td colspan="{$colSpan+1}">
            <pre>{$row|var_export}</pre>
        </td>
    </tr>
{/if}
</tbody>
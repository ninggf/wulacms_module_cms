<div class="wulaui">
    {if $rows}
        <ul class="nav nav-pills nav-stacked no-radius" data-pop-menu="#field-pop-menu">
            {foreach $rows as $row}
                <li data-id="{$row.id}">
                    <a href="javascript:void(0);">{if $row.row}[{$row.row}]{/if}{$row.label}({$row.name})</a>
                </li>
            {/foreach}
        </ul>
    {/if}
</div>
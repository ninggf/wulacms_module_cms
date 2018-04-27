<div class="container-fluid wulaui m-t-sm">
    <form id="edit-item" name="EditItemForm" data-validate="{$rules|escape}" action="{'cms/block/item/save'|app}"
          data-ajax method="post" data-loading>
        {$form|render}
    </form>
</div>
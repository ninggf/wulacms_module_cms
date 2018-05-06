<div class="container-fluid wulaui m-t-sm">
    <form id="edit-form" name="EditForm" data-validate="{$rules|escape}" action="{'cms/tag/save'|app}" data-ajax
          method="post" data-loading>
        {$form|render}
    </form>
</div>
<div class="container-fluid m-t-md">
    <div class="row wulaui">
        <div class="col-sm-12">
            <form id="edit-form" name="EditForm" action="{'cms/model/field/save'|app}" data-validate="{$rules|escape}"
                  data-ajax method="post" role="form" class="form-horizontal" data-loading>
                {$form|render}
            </form>
        </div>
    </div>
</div>
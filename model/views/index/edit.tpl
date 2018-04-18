<div class="container-fluid m-t-md">
    <div class="row wulaui">
        <div class="col-sm-12">
            <form id="edit-form" name="EditForm" action="{'cms/model/save'|app}"
                  data-validate="{$rules|escape}" data-ajax method="post" role="form" class="form-horizontal"
                  data-loading>
                <input type="hidden" name="id" id="id" value="{$id}"/>
                {$form|render}
            </form>
        </div>
    </div>
</div>
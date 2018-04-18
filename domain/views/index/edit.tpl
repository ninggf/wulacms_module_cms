<div class="container-fluid m-t-md">
    <div class="row wulaui">
        <div class="col-sm-12">
            <form id="core-admin-form" name="SettingForm" action="{'cms/domain/save'|app}"
                  data-validate="{$rules|escape}" data-ajax method="post" role="form" class="form-horizontal"
                  data-loading>
                <input type="hidden" name="id" id="id" value="{$id}"/>
                {$form|render}
            </form>
        </div>
    </div>
</div>
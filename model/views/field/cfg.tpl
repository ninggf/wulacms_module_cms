<div class="container-fluid m-t-xs">
    <div class="row wulaui">
        <div class="col-sm-12">
            <form id="cfg-form" name="CfgForm" action="{'cms/model/field/cfg'|app}"
                  {if $rules}data-validate="{$rules|escape}"{/if}
                  data-ajax method="post" role="form" class="form-horizontal" data-loading>
                <input type="hidden" name="id" value="{$id}"/>
                {if $fform}
                    <div class="line line-dashed line-lg pull-in"></div>
                    <p class="text-muted m-t-n-md">属性配置</p>
                    {$fform|render}
                {/if}
                {if $dsForm}
                    <div class="line line-dashed line-lg pull-in"></div>
                    <p class="text-muted m-t-n-md">数据源配置</p>
                    {$dsForm|render}
                {/if}
            </form>
        </div>
    </div>
</div>
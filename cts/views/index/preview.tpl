<div class="hbox stretch wulaui">
    <aside class="aside aside-lg b-r">
        <div class="p-xs">
            <form data-table-form="#preview-table">
                {$form|render}
                <div class="form-group">
                    <div class="col-xs-12">
                        <button type="reset" id="reset-btn" class="btn btn-default">重置</button>
                        <button type="submit" class="btn btn-primary">预览</button>
                    </div>
                </div>
            </form>
        </div>
    </aside>
    <section>
        <table id="preview-table" data-table="{'cms/cts/data'|app}/{$ds}">
            <thead>
            <tr>
                <th width="50">NO.</th>
                {foreach $cols as $col}
                    <th>{$col}</th>
                {/foreach}
            </tr>
            </thead>
        </table>
    </section>
</div>
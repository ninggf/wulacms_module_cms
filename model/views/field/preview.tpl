<div class="wulaui p-md">
    {if $form}
        <form data-ajax method="post" role="form" class="form-horizontal">
            {$form|render}
        </form>
    {else}
        <p class="text-muted text-center">{$msg}</p>
    {/if}
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="panel">
        <h3>{l s='Hotfix title ?' mod='hotfix'}</h3>
        {if $isLinux !== true}
            <div class="alert alert-success">
                {l s='Module successfully installed. Your shop benefits from the latest security update!' mod='hotfix'}
            </div>
        {else}
            <div class="alert alert-danger">
                {l s='This module is not compatible with your server configuration. Today, shops hosted on Windows servers cannot use this module.[1]Please check the module’s configuration page to see how you can apply this patch to your shop.' tags=['<br />'] mod='hotfix'}
            </div>
        {/if}
        </div>
    </div>
</div>

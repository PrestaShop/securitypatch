<div class="row">
    <div class="col-lg-12">
        <div class="panel">
        <h3>{l s='Security Patch' mod='hotfix'}</h3>
        {if $isLinux == true}
            <div class="alert alert-success">
                {l s='Module successfully installed. Your shop benefits from the latest security update!' mod='hotfix'}
            </div>
        {else}
            <div class="alert alert-danger">
                {l s='This module is not compatible with your server configuration. Today, shops hosted on Windows servers cannot use this module.' mod='hotfix'}<br />
                {l s='Please check the module’s configuration page to see how you can apply this patch to your shop.' mod='hotfix'}
            </div>
        {/if}
        </div>
    </div>
</div>

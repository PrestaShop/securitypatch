<h2>{l s='Hotfix title ?' mod='hotfix'}</h2>
<fieldset>
    {if $isLinux == true}
        <div class="conf ok">
            {l s='Module successfully installed. Your shop benefits from the latest security update!' mod='hotfix'}
        </div>
    {else}
        <div class="alert">
            {l s='This module is not compatible with your server configuration. Today, shops hosted on Windows servers cannot use this module.' mod='hotfix'}<br />
            {l s='Please check the module’s configuration page to see how you can apply this patch to your shop.' mod='hotfix'}
        </div>
    {/if}
</fieldset>

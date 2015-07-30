<h2>{l s='Security Patch' mod='securitypatch'}</h2>
<fieldset>
    {if $isLinux == true}
        {if !$execAvailable || !$execSuccess}
            <div class="alert">
                <b>{l s='The security update could not be applied to your shop. The module cannot execute the patch on your server configuration.' mod='securitypatch'}</b><br />
                {l s='Please check the details below for each update to see how you can implement the patch on your shop.' mod='securitypatch'}
            </div>
        {else}
            <div class="conf ok">
                {l s='Module successfully installed. Your shop benefits from the latest security update!' mod='securitypatch'}
            </div>
            <p>
                {l s='The module has applied the following patches to your store:' mod='securitypatch'}
            </p>
        {/if}
    {else}
        <div class="alert">
            <b>{l s='Your shop is hosted on a Windows server. Unfortunately, the module is not compatible with this configuration yet.' mod='securitypatch'}</b><br />
            {l s='Please check the details below for each update to see how you can implement the patch on your shop.' mod='securitypatch'}
        </div>
    {/if}
    <p>
        <b>{l s='Password generation update' mod='securitypatch'}</b> - {l s='July 2015' mod='securitypatch'}<br>
        {l s='Improved algorithm for password generation.' mod='securitypatch'} <a href="{$link}">{l s='Read this article' mod='securitypatch'}</a> {l s='for more details.' mod='securitypatch'}
    </p>
</fieldset>

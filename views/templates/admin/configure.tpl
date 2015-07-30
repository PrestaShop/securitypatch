<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <h3>{l s='Security Patch' mod='hotfix'}</h3>
            {if $isLinux == true}
                {if !$execAvailable}
                    <div class="alert alert-danger">
                        <b>{l s='The security update could not be applied to your shop. The module cannot execute the patch on your server configuration.' mod='hotfix'}</b><br />
                        {l s='Please check the details below for each update to see how you can implement the patch on your shop.' mod='hotfix'}
                    </div>
                {else}
                    <div class="alert alert-success">
                        {l s='Module successfully installed. Your shop benefits from the latest security update!' mod='hotfix'}
                    </div>
                    <p>
                        {l s='The module has applied the following patches to your store:' mod='hotfix'}
                    </p>
                {/if}
            {else}
                <div class="alert alert-danger">
                    <b>{l s='Your shop is hosted on a Windows server. Unfortunately, the module is not compatible with this configuration yet.' mod='hotfix'}</b><br />
                    {l s='Please check the details below for each update to see how you can implement the patch on your shop.' mod='hotfix'}
                </div>
            {/if}
            <p>
                <b>{l s='Password generation update' mod='hotfix'}</b> - {l s='July 2015' mod='hotfix'}<br>
                {l s='Improved algorithm for password generation.' mod='hotfix'} <a href="{$link}">{l s='Read this article' mod='hotfix'}</a> {l s='for more details.' mod='hotfix'}
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="panel">
            <h3>{l s='Security Patch' mod='hotfix'}</h3>
            {if $isLinux == true}
                <div class="alert alert-success">
                    {l s='Module successfully installed. Your shop benefits from the latest security update!' mod='hotfix'}
                </div>
                <p>
                    {l s='The module has applied the following patches to your store:' mod='hotfix'}
                </p>
            {else}
                <div class="alert alert-danger">
                    <b>{l s='This module is not compatible with your server configuration. Today, shops hosted on Windows servers cannot use this module.' mod='hotfix'}</b><br />
                    {l s='Please check the module’s configuration page to see how you can apply this patch to your shop.' mod='hotfix'}
                </div>
            {/if}
            <p>
                <b>{l s='Password generation update' mod='hotfix'}</b> - {l s='July 2015' mod='hotfix'}<br>
                {l s='Improved algorithm for password generation.' mod='hotfix'} <a href="#">{l s='Read this article' mod='hotfix'}</a> {l s='for more details.' mod='hotfix'}
            </p>
        </div>
    </div>
</div>

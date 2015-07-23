{**
 * 2007-2015 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2015 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<div class="panel" id="hotfix-panel">
    <div class="row">
        <div class="col-md-6">
            <h1>{l s='Here is the super title' mod='hotfix'}</h1>
            <p>{l s='Here is the description of the hotfix and why?[1][1]Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent quis pulvinar libero, at facilisis arcu. Aliquam tempus metus in congue aliquam. Praesent viverra sollicitudin convallis. Morbi suscipit feugiat justo id pulvinar. Mauris lectus metus, convallis ut egestas vitae, mattis et justo. Nulla et mi lorem. Pellentesque nibh nibh, gravida dapibus pellentesque eget, fermentum eu justo. Morbi diam nibh, vulputate a maximus ac, placerat ac ligula. Etiam eget enim dolor. Fusce a urna ac ligula consectetur convallis. Fusce tortor leo, malesuada eget tempor at, rhoncus vel ante.' tags=['<br />'] mod='hotfix'}</p>
        </div>
        <div class="col-md-6 text-center">
            <p>
                <a class="btn btn-primary btn-lg" href="javascript:StartPatches();">{l s='The big owl button for start!' mod='hotfix'}</a>
            </p>
            <div class="content hotfix-list">
                {foreach from=$patches item=patch}
                    <div id="hotfix-{$patch['id_hotfix_patche']}">
                        <div class="row hotfix-todo" {if $patch['status'] != 0}style="display: none;"{/if}>
                            <div class="col-md-1"></div>
                            <div class="name col-md-3 text-left"><strong>{$patch['guid']}</strong></div>
                            <div class="col-md-3 text-right">{$patch['date']}</div>
                            <div class="col-md-3 text-right status">TODO <i class="icon-exclamation-sign"></i></div>
                        </div>
                        <div class="row hotfix-done" {if $patch['status'] != 1}style="display: none;"{/if}>
                            <div class="col-md-1"></div>
                            <div class="name col-md-3 text-left"><strong>{$patch['guid']}</strong></div>
                            <div class="col-md-3 text-right">{$patch['date']}</div>
                            <div class="col-md-3 text-right status">Done <i class="icon-check"></i></div>
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function StartPatches()
    {
        InstallNextPatch();
    }
    function InstallNextPatch()
    {
        $.ajax({
            type: 'POST',
            url: '{$module_path}',
            dataType: 'json',
            data: {
                controller : 'AdminHotfix',
                action : 'installPatch',
                ajax : true
            },
            success: function(json) {
                if (json.success == true) {
                    var hotfixRow = $('#hotfix-'+json.hotfix_id);
                    hotfixRow.find('.hotfix-todo').hide();
                    hotfixRow.find('.hotfix-done').show();

                    if (json.finished == false) {
                        InstallNextPatch();
                    }
                }
            }
        });
    }
</script>


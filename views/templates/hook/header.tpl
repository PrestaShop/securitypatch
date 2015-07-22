<div id="hotfix-header" style="display: none;">
    {if $count == 1}
        {l s='A hotfixes need to be installed. [1]Click here to proceed[/1].' tags=['<a>'] module='hotfix'}
    {elseif $count > 1}
        {l s='%s hotfixes need to be installed. [1]Click here to proceed[/1].' tags=['<a>'] sprintf=[$count] module='hotfix'}
    {/if}
</div>
<script type="text/javascript">
    $(function(){
        $("body").prepend($("#hotfix-header").show());
    });
</script>

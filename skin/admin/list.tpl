{extends file="layout.tpl"}
{block name='body:id'}plugins-{$pluginName}{/block}
{block name="article:content"}
    <h1><span class="fa fa-credit-card"></span> Hipay</h1>
    <div class="mc-message clearfix"></div>
    {include file="forms/edit.tpl"}
{/block}
{block name="modal"}
    <div id="window-dialog"></div>
{/block}
{block name='javascript'}
    {include file="js.tpl"}
{/block}
{extends file="layout.tpl"}
{block name='head:title'}hipay{/block}
{block name='body:id'}hipay{/block}
{block name='article:header'}
    <h1 class="h2">Hipay</h1>
{/block}
{block name='article:content'}
    {if {employee_access type="view" class_name=$cClass} eq 1}
        <div class="panels row">
            <section class="panel col-ph-12">
                {if $debug}
                    {$debug}
                {/if}
                <header class="panel-header">
                    <h2 class="panel-heading h5">Hipay</h2>
                </header>
                <div class="panel-body panel-body-form">
                    <div class="mc-message-container clearfix">
                        <div class="mc-message"></div>
                    </div>

                    <div class="row">
                        <form id="hipay_config" action="{$smarty.server.SCRIPT_NAME}?controller={$smarty.get.controller}&amp;action=edit" method="post" class="validate_form edit_form col-xs-12 col-md-6">
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="wsLogin">wsLogin :</label>
                                        <input type="text" class="form-control" id="wsLogin" name="wsLogin" value="{$hipay.wslogin}" size="50" />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="wsPassword">wsPassword :</label>
                                        <input type="text" class="form-control" id="wsPassword" name="wsPassword" value="{$hipay.wspassword}" size="50" />
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="websiteId">websiteId* :</label>
                                        <input type="text" class="form-control required" id="websiteId" name="websiteId" value="{$hipay.websiteid}" size="50" />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="formaction">Form Action* :</label>
                                        <select name="formaction" id="formaction" class="form-control required" required>
                                            <option value="">Sélectionner une action</option>
                                            <option value="test"{if $hipay.formaction eq 'test'} selected{/if}>Test</option>
                                            <option value="production"{if $hipay.formaction eq 'production'} selected{/if}>Production</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                        <label for="signkey">Signkey :</label>
                                        <input type="text" class="form-control" id="signkey" name="signkey" value="{$hipay.signkey}" size="50" />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-6">
                                    <div class="form-group">
                                        <div class="switch">
                                            <input type="checkbox" id="active" name="direct" class="switch-native-control"{if $hipay.direct} checked{/if} />
                                            <div class="switch-bg">
                                                <div class="switch-knob"></div>
                                            </div>
                                        </div>
                                        <label for="direct">Achat direct</label>
                                    </div>
                                </div>
                            </div>
                            <div id="category" class="collapse {if $hipayCategory != NULL}in{/if}">
                                <div class="row">
                                    {include file="loop/category.tpl" hipayCategory=$hipayCategory}
                                </div>
                            </div>
                            <div id="submit">
                                <button class="btn btn-main-theme" type="submit" name="action" value="edit">{#save#|ucfirst}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    {else}
        {include file="section/brick/viewperms.tpl"}
    {/if}
{/block}
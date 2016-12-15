{assign var="collectionformAction" value=[
"test"=>"Test",
"production"=>"Production"
]}
<form id="forms_plugins_hipay" method="post" action="">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="pspid_og">wsLogin* :</label>
                <input type="text" class="form-control" id="wsLogin" name="wsLogin" value="{$dataHipay.wsLogin}" size="50" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
            <label for="wsPassword">wsPassword* :</label>
            <input type="text" class="form-control" id="wsPassword" name="wsPassword" value="{$dataHipay.wsPassword}" size="50" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="websiteId">websiteId* :</label>
                <input type="text" class="form-control" id="websiteId" name="websiteId" value="{$dataHipay.websiteId}" size="50" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="customerIpAddress">customerIpAddress* :</label>
                <input type="text" class="form-control" id="customerIpAddress" name="customerIpAddress" value="{$dataHipay.customerIpAddress}" size="50" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="signkey">Signkey :</label>
                <input type="text" class="form-control" id="signkey" name="signkey" value="{$dataHipay.signkey}" size="50" />
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="formaction">Action* :</label>
                <select class="form-control" id="formaction" name="formaction">
                    <option value="">Sélectionner une action</option>
                    {foreach $collectionformAction as $key => $value}
                        {$selected  =   ''}
                        {if $dataHipay.formaction == $key}
                            {$selected  =   ' selected'}
                        {/if}
                        <option{$selected} value="{$key}">{$value}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="categoryId">Categorie* :</label>
                <select class="form-control" id="categoryId" name="categoryId">
                    <option value="">Sélectionner votre thématique secondaire (Voir Hipay)</option>
                    {foreach $getCategory as $key => $value}
                        {$selected  =   ''}
                        {if $dataHipay.categoryId == $value.id}
                            {$selected  =   ' selected'}
                        {/if}
                        <option{$selected} value="{$value.id}">{$value.name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </div>

    <div class="btn-row">
        <input type="submit" class="btn btn-primary" value="{#save#|ucfirst}" />
    </div>
</form>
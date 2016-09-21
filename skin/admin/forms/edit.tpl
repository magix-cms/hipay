{assign var="collectionformAction" value=[
"test"=>"Test",
"production"=>"Production"
]}
<form id="forms_plugins_hipay" method="post" action="">
    <div class="row">
        <div class="form-group">
            <div class="col-lg-6 col-md-6">
                <label for="pspid_og">wsLogin* :</label>
                <input type="text" class="form-control" id="wsLogin" name="wsLogin" value="{$dataHipay.wsLogin}" size="50" />
            </div>
            <div class="col-lg-6 col-md-6">
                <label for="wsPassword">wsPassword* :</label>
                <input type="text" class="form-control" id="wsPassword" name="wsPassword" value="{$dataHipay.wsPassword}" size="50" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            <div class="col-lg-6 col-md-6">
                <label for="websiteId">websiteId* :</label>
                <input type="text" class="form-control" id="websiteId" name="websiteId" value="{$dataHipay.websiteId}" size="50" />
            </div>
            <div class="col-lg-6 col-md-6">
                <label for="customerIpAddress">customerIpAddress* :</label>
                <input type="text" class="form-control" id="customerIpAddress" name="customerIpAddress" value="{$dataHipay.customerIpAddress}" size="50" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            <div class="col-lg-6 col-md-6">
                <label for="signkey">Signkey :</label>
                <input type="text" class="form-control" id="signkey" name="signkey" value="{$dataHipay.signkey}" size="50" />
            </div>
            <div class="col-lg-6 col-md-6">
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

    <div class="btn-row">
        <input type="submit" class="btn btn-primary" value="{#send#|ucfirst}" />
    </div>
</form>
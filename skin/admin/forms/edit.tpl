{assign var="collectionformAction" value=[
"test"=>"Test",
"production"=>"Production"
]}
<form id="forms_plugins_hipay" method="post" action="">
    <div class="row">
        <div class="form-group">
            <div class="col-lg-6 col-md-6">
                <label for="pspid_og">Mailhack  :</label>
                <input type="text" class="form-control" id="mailhack" name="mailhack" value="{$dataHipay.mailhack}" size="50" />
            </div>
            <div class="col-lg-6 col-md-6">
                <label for="pwaccount">Pwaccount :</label>
                <input type="text" class="form-control" id="pwaccount" name="pwaccount" value="{$dataHipay.pwaccount}" size="50" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            <div class="col-lg-6 col-md-6">
                <label for="setaccount">Setaccount :</label>
                <input type="text" class="form-control" id="setaccount" name="setaccount" value="{$dataHipay.setaccount}" size="50" />
            </div>
            <div class="col-lg-6 col-md-6">
                <label for="setmarchantsiteid">Setmarchantsiteid :</label>
                <input type="text" class="form-control" id="setmarchantsiteid" name="setmarchantsiteid" value="{$dataHipay.setmarchantsiteid}" size="50" />
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group">
            <div class="col-lg-6 col-md-6">
                <label for="mailcart">Mailcart :</label>
                <input type="text" class="form-control" id="mailcart" name="mailcart" value="{$dataHipay.mailcart}" size="50" />
            </div>
            <div class="col-lg-6 col-md-6">
                <label for="setcategory">Setcategory :</label>
                <input type="text" class="form-control" id="setcategory" name="setcategory" value="{$dataHipay.setcategory}" size="50" />
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
                <label for="formaction">Action :</label>
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
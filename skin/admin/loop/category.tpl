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
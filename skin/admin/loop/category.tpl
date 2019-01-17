<div class="col-xs-12 col-sm-6">
    <div class="form-group">
        <label for="categoryId">{#category#}* :</label>
        <select name="categoryId" id="categoryId" class="form-control">
            <option value="">Sélectionner votre thématique secondaire (Voir Hipay)</option>
            {foreach $hipayCategory as $key => $value}
                {$selected  =   ''}
                {if $hipay.categoryid == $value.id}
                    {$selected  =   ' selected'}
                {/if}
                <option{$selected} value="{$value.id}">{$value.name}</option>
            {/foreach}
        </select>
    </div>
</div>
<form id="hipay-form" class="validate nice-form" method="post" action="{$url}/{$lang}/hipay/">
    <input id="amount" type="hidden" name="purchase[amount]" class="form-control required" value="" />
    {*<input id="shipping" type="hidden" name="custom[shipping]" class="form-control required" value="" />*}
    <button type="submit" class="btn btn-box btn-block btn-invert-white">{#pn_product_send#|ucfirst}</button>
</form>
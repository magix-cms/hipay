{extends file="mail/layout.tpl"}
<!-- Wrapper/Container Table: Use a wrapper table to control the width and the background color consistently of your email. Use this approach instead of setting attributes on the body tag. -->
{block name='body:content'}
    <!-- move the above styles into your custom stylesheet -->
    <table align="center" class="vignette container content float-center">
        <tbody>
        <tr>
            <td>
                <table class="spacer {*spacer-hr*}">
                    <tbody>
                    <tr>
                        <td height="16px" style="font-size:16px;line-height:16px;">&#xA0;</td>
                    </tr>
                    </tbody>
                </table>
                <table class="row">
                    <tbody>
                    <tr>
                        <td class="small-12 large-12 columns first last">
                            <table>
                                <tr>
                                    <td>
                                        <h4>{$data.title}</h4>
                                        <p>{$data.content|replace:'\n':'<br />'}</p>
                                    </td>
                                    <td class="expander"></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="small-12 large-12 first last">
                            <table class="spacer spacer-hr">
                                <tbody>
                                <tr>
                                    <td height="16px" style="font-size:16px;line-height:16px;">&#xA0;</td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="small-12 large-6 columns first last">
                            <table>
                                <tr>
                                    <td>
                                        <h5>{#mail_from#|ucfirst} {$companyData.name}</h5>
                                        <p>
                                            <a href="{$data.url}" target="_blank" title="{$data.productname}" style="text-decoration: none; font-size: 46px; padding: 15px;">
                                                <img src="{$data.img}" alt="{$data.productname}"/>
                                            </a>
                                        </p>
                                        <p>{$data.lastname|ucfirst} {$data.firstname|ucfirst}</p>
                                        {if $data.address != null}
                                            <p>{$data.address|ucfirst}, {$data.postcode} {$data.city}</p>
                                        {/if}
                                        {*{if $data.phone != null}
                                            <p>{#mail_phone#|ucfirst}&nbsp;: {$data.phone}</p>
                                        {/if}*}
                                        <p>{#mail_email#|ucfirst}&nbsp;: <a href="mailto:{$data.email}">{$data.email}</a></p>
                                        <p>{#product_name#} : {$data.productname}</p>
                                        <p>{#content#} : {$data.content|replace:'\n':'<br />'}</p>
                                        <p>{#total_mail_amount#} : {$data.amount}€</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
{/block}
<!-- End of wrapper table -->
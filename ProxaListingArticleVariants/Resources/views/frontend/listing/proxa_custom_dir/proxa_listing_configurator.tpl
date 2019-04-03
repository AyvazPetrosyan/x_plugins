{namespace name='frontend/listing/product-box/box-basic'}
{$sConfiguratorList = $sArticle.sConfigurator}
{$optionsGroups = $sConfiguratorList['optionsGroup']}
{*{if !empty($sConfiguratorList)}*}
    <div class="proxa--product-configurator-block product--configurator is--hidden">
        <form name="sAddToBasket"
              method="post"
              action="{url controller=checkout action=addArticle}"
              class="buybox--form" data-add-article="true"
              data-eventName="submit"
                {if $theme.offcanvasCart}
                    data-showModal="false"
                    data-addArticleUrl="{url controller=checkout action=ajaxAddArticleCart}"
                {/if}
        >
            <input class="articleID--field" type="hidden" value="{$sArticle['articleID']}" />
            {foreach from=$optionsGroups item=optionsGroup key=groupName}
                {$groupOptions = $optionsGroup['values']}
                <div class="proxa--listing-variant-block">
                    <label class="proxa--option-group-name">{$groupName}</label></br>
                    <select data-url="{url controller = 'VariantAjaxChange'}"
                            data-detail=""
                            name="{$optionsGroup['groupID']}"
                            class="proxa--variant-select"
                    >
                        <option value="defaultValue" disabled selected="selected">{s name="ProxaOptionDefoultValue"}Please select{/s}</option>
                        {foreach from=$groupOptions item=option key=optionKay}
                            <option value='{$option['optionID']}'
                                    class='select--option'
                            >{$option['optionName']}</option>
                        {/foreach}
                    </select>
                </div>
            {/foreach}
            {*<input type="hidden" name="__csrf_token" value="hSjvC0zMm6gykBtVsMWOdlzbLF9VMK">*}
            <input class="proxa--product-order-number-{$sArticle["articleID"]}" type="hidden" name="sAdd" value="{$sArticle["ordernumber"]}" />

            {* Quantity selection *}
            <div class="proxa--buybox-quantity buybox--quantity block">
                {$maxQuantity=$sArticle.maxpurchase+1}
                {if $sArticle.laststock && $sArticle.instock < $sArticle.maxpurchase}
                    {$maxQuantity=$sArticle.instock+1}
                {/if}

                <label class="proxa--quantity-label">{s name='proxaQuantityLabel'}Count{/s}</label></br>
                <div class="select-field">
                    <select id="sQuantity" name="sQuantity" class="quantity--select" title="">
                        {section name="i" start=$sArticle.minpurchase loop=$maxQuantity step=$sArticle.purchasesteps}
                            <option value="{$smarty.section.i.index}">{$smarty.section.i.index}{if $sArticle.packunit} {$sArticle.packunit}{/if}</option>
                        {/section}
                    </select>
                </div>
            </div>

            {* "Buy now" button *}
            <button class="buybox--button block btn is--primary is--icon-right {if !empty($optionsGroups)}is--disabled{/if} is--center is--largee"
                    aria-disabled="true"
                    name="{s name="DetailBuyActionAddName"}{/s}"
                    {if !empty($optionsGroups)}
                        disabled="disabled"
                    {/if}
                    style="font-size: 13px;"
            >
                {s name="DetailBuyActionAdd"}{/s} <i class="icon--arrow-right"></i>
            </button>
        </form>
    </div>
{*{/if}*}
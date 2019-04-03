{extends file="parent:frontend/listing/product-box/box-basic.tpl"}

{block name='frontend_listing_box_article_info_container'}
    {$smarty.block.parent}
    {if $productBoxLayout != 'slider'}
        {include file="frontend/listing/proxa_custom_dir/proxa_listing_configurator.tpl"}
    {/if}
{/block}
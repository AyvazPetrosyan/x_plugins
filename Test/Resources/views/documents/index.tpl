{extends file="parent:documents/index.tpl"}

{block name="document_index_info"}
    {foreach $Order._order.attributes as $key=>$value}
        {$value}<br>
    {/foreach}
    {$smarty.block.parent}
{/block}
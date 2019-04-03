{extends file='parent:frontend/listing/product-box/box-basic.tpl'}

{block name='frontend_listing_box_article_info_container'}
    {$smarty.block.parent}
    {$additionaltext = $sArticle.additionaltext}
    {$additionaltextList = ","|explode:$additionaltext}
    <div class="colors--block">
        {foreach $additionaltextList as $additionaltext}
            {$colorInfoList = "<>"|explode:$additionaltext}
            <a href="{$colorInfoList[1]}" class="color-content">
                <div class="color--area"
                     style="width: 20px;
                            height: 20px;
                            border: 1px solid black;
                            background-color: {$colorInfoList[0]};
                            margin: 2px;
                            float: left"
                >
                </div>
            </a>
        {/foreach}
    </div>
{/block}
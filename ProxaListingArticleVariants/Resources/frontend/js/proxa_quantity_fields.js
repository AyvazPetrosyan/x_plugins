;(function ($, window, StateManager) {
    $(function (){
        // console.log("start");
        AjaxQuery.init();

    });

    var AjaxQuery = {

        init: function (){
            // console.log("init");
            var self = this;

            self.registerShopwareEvents();

            self.applyConfiguratorChange();
            $.publish("plugin/PreviousAndLastOrders/onInit", [self]);
        },

        registerShopwareEvents: function () {
            var self = this;

            /*$.subscribe('plugin/swInfiniteScrolling/onFetchNewPageFinished', function(){
                StateManager.addPlugin('select:not([data-no-fancy-select="true"])', 'swSelectboxReplacement');
            });*/
            $.subscribe('plugin/swInfiniteScrolling/onFetchNewPageFinished', $.proxy(self.onFetchNewPageFinished, self));
            $.subscribe('plugin/swListingActions/onGetFilterResultFinished', $.proxy(self.onGetFilterResultFinished, self));
        },

        onFetchNewPageFinished: function (event, plugin, template) {
            var self = this;
            if(template) {
                self.applyConfiguratorChange(template);
                self.applyFancySelectPlugin();
            }
        },

        onGetFilterResultFinished: function () {
            var self = this;

            self.applyConfiguratorChange();
            self.applyAddArticlePlugin();
            self.applyFancySelectPlugin();
        },

        applyConfiguratorChange: function (template){
            // console.log('function registerEventChange');
            var self = this;
            if (template) {
                var $template = $.parseHTML(template);
                $.each($template, function (key, value) {
                    if ($(value).hasClass("product--box")) {
                        $(value).find($("select.proxa--variant-select")).change(function (event) {
                            var thisHtmlElement = $(this);
                            self.sendAjaxQuery(thisHtmlElement);
                        });
                    }
                })

            } else {
                $("select.proxa--variant-select").change(function (event) {
                    var thisHtmlElement = $(this);
                    self.sendAjaxQuery(thisHtmlElement);
                });
            }
        },

        sendAjaxQuery: function (thisHtmlElement) {
            var self = this;
            var url = $(thisHtmlElement).data("url");
            var parentHtmlElement = $(thisHtmlElement).parents(".product--box");
            var ajaxList = self.getSelectValueInfo(parentHtmlElement);
            //var jsonAjaxList = JSON.stringify(ajaxList);
            $.ajax({
                url: url,
                method: 'POST',
                data: ajaxList,
                success: function (result) {
                    console.log(result);
                    self.setProductInfo(result, parentHtmlElement);
                }
            });
        },

        getSelectValueInfo: function (parentHtmlElement){
            var self = this;
            var htmlSelectedOptionInfo = {};
            var group = {};
            var articleID = $(parentHtmlElement).find('.articleID--field').val();
            var selectHtmlElementList = $(parentHtmlElement).find('select.proxa--variant-select');
            $.each(selectHtmlElementList, function (key, value) {
                group[$(value).attr('name')] = $(value).val();
            });
            htmlSelectedOptionInfo.group = group;
            htmlSelectedOptionInfo.articleID = articleID;

            return htmlSelectedOptionInfo;
        },

        setProductInfo: function(productInfo, variantFieldsParentHtmlElement){
            var self = this;
            var productInfoParsed = JSON.parse(productInfo);
            var productOrderNumber = productInfoParsed['orderNumber'];
            var productDetailPrice = productInfoParsed['price'];
            var articleID = $(variantFieldsParentHtmlElement).find("input.articleID--field").val();
            $(".proxa--product-order-number-"+articleID).val(productOrderNumber);
            $(variantFieldsParentHtmlElement).find('.proxa-price').html(productDetailPrice);
        },

        applyFancySelectPlugin: function () {
            var self =  this;
            StateManager.addPlugin('select:not([data-no-fancy-select="true"])', 'swSelectboxReplacement');
        },
        applyAddArticlePlugin: function () {
            var self =  this;
            StateManager.addPlugin('*[data-add-article="true"]', 'swAddArticle');
        }
    }
})(jQuery, window, window.StateManager);
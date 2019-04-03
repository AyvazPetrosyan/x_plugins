;(function ($) {
    $(function (){
        // console.log("start");
        Script.init();
    });

    var Script = {

        htmlProxaVariantSelect: $(".proxa--variant-select"),
        htmlProxaProductConfiguratorBlock: $(".proxa--product-configurator-block"),
        htmlBoxContent: $(".box--content"),

        init: function (){
            // console.log("init");
            var self = this;

            self.registerEventHovver(self.htmlBoxContent, self);
            self.registerEventChange(self.htmlProxaVariantSelect);

            $.subscribe('plugin/swInfiniteScrolling/onFetchNewPageFinished', function(){
                self.registerEventHovver($(".box--content"), self),
                self.registerEventChange($(".proxa--variant-select"));
            });

            $.subscribe('plugin/swListingActions/onGetFilterResultFinished', function() {
                self.registerEventHovver($(".box--content"), self),
                self.registerEventChange($(".proxa--variant-select"), self)
            });


            $.subscribe('plugin/swListingActions/onCreateActiveFiltersFromCategoryParams', function (){
                self.registerEventHovver($(".box--content"), self),
                self.htmlProxaVariantSelect.change(function (event){
                    var parentHtmlElement = $(event.target).parent().parent().parent();
                    var htmlCheckoutButton = parentHtmlElement.find(".buybox--button");
                    if(self.isValid(parentHtmlElement)){
                        self.enableHtmlElement(htmlCheckoutButton);
                    } else {
                        self.disableHtmlElement(htmlCheckoutButton);
                    }
                });
            });

            $.publish("plugin/PreviousAndLastOrders/onInit", [self]);
        },

        registerEventHovver: function (htmlElement, self){
            htmlElement.hover(
                function (){
                    // console.log("event mouseover");
                    var ConfiguratorBlock = $(this).find(".proxa--product-configurator-block");
                    self.showHtmlElement(ConfiguratorBlock);
                }, function () {
                    // console.log("event mouseout");
                    var ConfiguratorBlock = $(this).find(".proxa--product-configurator-block");
                    self.hideHtmlElement(ConfiguratorBlock);
                }
            );
        },

        registerEventChange: function (htmlElement){
            // console.log("function registerEventChange");
            var self = this;
            htmlElement.change(function (event){
                var parentHtmlElement = $(event.target).parent().parent().parent();
                var htmlCheckoutButton = parentHtmlElement.find(".buybox--button");
                if(self.isValid(parentHtmlElement)){
                    self.enableHtmlElement(htmlCheckoutButton);
                } else {
                    self.disableHtmlElement(htmlCheckoutButton);
                }
            });
        },

        isValid: function (parentHtmlElement){
            // console.log("function isValid");
            var selectElementList = parentHtmlElement.find("select.proxa--variant-select");
            var selectElementListCount = selectElementList.length;
            for(var step=0; step<selectElementListCount; step++){
                var selectHtmlElement = selectElementList[step];
                var selectValue = $(selectHtmlElement).val();
                if(selectValue == null){
                    return false;
                    break;
                }
            }
            return true;
        },

        disableHtmlElement: function(htmlElement){
            // console.log("function disableHtmlElement");
            htmlElement.addClass("is--disabled");
            htmlElement.attr("disabled","disabled");
        },

        enableHtmlElement: function(htmlElement){
            // console.log("function enableHtmlElement");
            htmlElement.removeClass("is--disabled");
            htmlElement.removeAttr("disabled");
        },

        showHtmlElement: function(htmlElement){
            // console.log("function showHtmlElement");
            htmlElement.removeClass('is--hidden');
        },

        hideHtmlElement: function(htmlElement){
            // console.log("function hideHtmlElement");
            htmlElement.addClass('is--hidden');
        },
    }
})(jQuery);
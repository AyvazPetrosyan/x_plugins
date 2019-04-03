;(function ($, window, StateManager) {

    // $(function () {
    //     var itemsListParentElementSelector = $(".sidebar--categories-navigation").children();
    //     var minItemsCount = 2;
    //     var morLessButtonSelector = ".mor--less-button";
    //
    //     ShowHideItems.init(itemsListParentElementSelector, minItemsCount, morLessButtonSelector);
    // });

    var ShowHideItems = {

        minItemsCount: 1,

        morLessButtonHtml: null,

        classes: {
            morLessButton: null,
        },

        selectors: {
            itemsListParentElementSelector: null,
            itemsList: null,
            morLessButtonSelector: null,
        },

        init: function (itemsListParentElementSelector, minItemsCount, morLessButtonSelector ) {
            var self = this;
            var selectors = self.selectors;
            var classes = self.classes;

            self.minItemsCount = minItemsCount;
            selectors.itemsListParentElementSelector = itemsListParentElementSelector;
            selectors.itemsList = $(itemsListParentElementSelector).children();
            selectors.morLessButtonSelector = morLessButtonSelector;

            var splitList = morLessButtonSelector.split(".");
            if(splitList.length>1){
                classes.morLessButton = splitList[1]
            }

            if($(selectors.itemsList).length>minItemsCount) {
                if ($(selectors.morLessButtonSelector).length == 0) {
                    self.createMoreLessButton();
                }
                self.hideItems();
                self.registerEvents();
            }
        },

        registerEvents: function () {
            var self = this;
            var selectors = self.selectors;

            $(selectors.morLessButtonSelector).click(function () {
                self.showHideItems();
            });
        },

        showHideItems: function () {
            var self = this;
            var selectors = self.selectors;

            var itemsList = selectors.itemsList;
            var activeItemsList = $(selectors.itemsList).not('.is--hidden');
            var activeItemsCount = activeItemsList.length;

            if (activeItemsCount > self.minItemsCount) {
                self.hideItems();
            } else {
                self.showItems();
            }
        },

        showItems: function () {
            var self = this;
            var selectors = self.selectors;
            var minItemsCount = self.minItemsCount;
            var itemsList = selectors.itemsList;

            $.each(itemsList, function (key, value) {
                if (key > minItemsCount - 1) {
                    $(value).removeClass('is--hidden');
                }
            });
            self.changeButtonValue('hide');
        },

        hideItems: function () {
            var self = this;
            var selectors = self.selectors;
            var minItemsCount = self.minItemsCount;
            var itemsList = selectors.itemsList;

            $.each(itemsList, function (key, value) {
                if (key > minItemsCount - 1) {
                    $(value).addClass('is--hidden');
                }
            });
            self.changeButtonValue('show');
        },

        changeButtonValue: function (param) {
            var self = this;
            var selectors = self.selectors;
            var morLessButtonSelector = selectors.morLessButtonSelector;

            if (param == 'show') {
                $(morLessButtonSelector).html("show more");
            } if (param == 'hide') {
                $(morLessButtonSelector).html("show less");
            }
        },
        
        createMoreLessButton: function () {
            var self = this;
            var selectors = self.selectors;
            var classes = self.classes;
            var itemsListParentElementSelector = selectors.itemsListParentElementSelector;

            var morLessButtonHtml = "<button class="+classes.morLessButton+"></button>";

            $(itemsListParentElementSelector).after(morLessButtonHtml);
        }
    }
})(jQuery, window, window.StateManager);
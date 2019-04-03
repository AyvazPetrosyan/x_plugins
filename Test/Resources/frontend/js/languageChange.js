;(function ($, window, StateManager) {

    $(function () {
        languageChange.init();
    });

    var languageChange = {
        opts: {

        },

        selectors: {
            body: "body",
            headerNavigation: ".header--navigation",
            languageFlag: ".header-main .field--select .language--flag-content",
            languagesContent: ".field--select .select-field",
        },

        init: function () {
            var self = this;
            var opts = self.opts;
            var selectors = self.selectors;

            self.subscribeJsEvents();
            self.hide(selectors.languagesContent);
        },

        subscribeJsEvents: function () {
            var self = this;
            var opts = self.opts;
            var selectors = self.selectors;

            $(selectors.languageFlag).click(function () {
                self.showHide(selectors.languagesContent)
            });

            $(selectors.body).click(function (event) {
                console.log($(event.target).attr('class'));
                var elementClass = $(event.target).attr('class');
                if(elementClass != "language--flag-content") {
                    self.hide(selectors.languagesContent);
                }
            });
        },

        showHide: function (elementSelector) {
            var self = this;
            var opts = self.opts;
            var selectors = self.selectors;

            // var isHidden = $(elementSelector).hasClass('is--hidden');
            var isHidden = $(elementSelector).css('visibility') == 'hidden'
            if (isHidden) {
                self.show(elementSelector);
            } else {
                self.hide(elementSelector);
            }
        },

        show: function (elementSelector) {
            // $(elementSelector).removeClass('is--hidden');
            $(elementSelector).css('visibility', 'visible');
        },

        hide: function (elementSelector) {
            // $(elementSelector).addClass('is--hidden');
            $(elementSelector).css('visibility', 'hidden');
        },
    }
})(jQuery, window, window.StateManager);
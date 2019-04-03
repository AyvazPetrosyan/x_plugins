;
(function ($) {
    $.fn.showHide = function(){

        var selectedElement = this;

        var showHide = {

            opts: {
                selectedElement: null,
                unit: 'px',
                minimizeSize: {
                    width: '50',
                    height: '50',
                },
                defaultTextElement: '<span class="default-text"></span>',
                defaultText: 'open',
            },

            defaults: {
                size: {
                    width: '',
                    height: '',
                },
                style: {
                    overflow: '',
                }
            },

            selectors: { },

            init: function () {
                var self = this;

                self.setDefaultOptions();
                self.hide();
                self.registerEvents();
            },

            setDefaultOptions: function () {
                var self = this;
                var defaults = self.defaults;

                defaults.size.width  = $(selectedElement).width();
                defaults.size.height = $(selectedElement).height();
                defaults.style.overflow = $(selectedElement).css('overflow');
            },

            registerEvents: function () {
                var self = this;

                selectedElement.hover(function () {
                    self.show();
                }, function () {
                    self.hide();
                });
            },

            hide: function () {
                var self = this;
                var opts = self.opts;
                var minimizeHeight = opts.minimizeSize.height;
                var unit = opts.unit;

                selectedElement.css({
                    'overflow': 'hidden',
                });

                selectedElement.animate({
                    'height': minimizeHeight+unit,
                });

                setTimeout(function(){
                    selectedElement.children().css({'visibility': 'hidden'});
                    selectedElement.prepend(opts.defaultTextElement);
                    $('.default-text').html(opts.defaultText)
                }, 500);
            },

            show: function () {
                var self = this;
                var defaults = self.defaults;

                selectedElement.animate({
                    'height': defaults.size.height,
                });

                /*selectedElement.css({
                    'overflow': defaults.style.overflow,
                });*/

                selectedElement.children().css({'visibility': 'visible'});

                $('.default-text').remove();
            },
        }

        showHide.init();
    }
})(jQuery);
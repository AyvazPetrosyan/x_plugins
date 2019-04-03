;(function ($, window, StateManager) {
    var Ajax = {

        ajaxSubmitElement: null,

        ajaxSendEvent: null,

        data: null,

        url: null,

        method: null,

        ajaxResult: null,

        init: function (ajaxSubmitElement, ajaxSendEvent, data, url, method){
            var self = this;
            if(method==null){
                method='POST';
            }
            self.ajaxSubmitElement = ajaxSubmitElement;
            self.ajaxSendEvent = ajaxSendEvent;
            self.data = data;
            self.url = url;
            self.method = method;

            self.registerEvents();
        },

        registerEvents: function (){
            var self = this;
            var ajaxSubmitElement = self.ajaxSubmitElement;
            var ajaxSendEvent = self.ajaxSendEvent;

            ajaxSubmitElement.on(ajaxSendEvent, function () {
                /*!!!!!!!!!!!!!!!!*/
                self.sendAjaxQuery();
            });
        },

        sendAjaxQuery: function () {
            var self = this;
            var data = self.data;
            var dataJson = JSON.stringify(data);
            var url = self.url;
            var method = self.method;

            $.ajax({
                url: url,
                method: method,
                data: {ajaxData: dataJson},
                success: function (result) {
                    $.publish('plugin/DisDetailForm/onLoadFormContentSuccess', [self]);
                    self.ajaxResult =  result;
                }
            });
        },
    }
})(jQuery, window, window.StateManager);
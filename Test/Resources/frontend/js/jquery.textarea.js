
var TextAreaMaxSize = {

    options: {
        textAreaCursorPosition: 0,
        rowMaxSize: 10,
        rowMaxCount: 30,
        oldRowList: [],
        newRowList: [],
    },

    selectors: {
        document: 'document',
        textAreaSelector: ''
    },

    init: function (textAreaSelector, rowMaxSize, rowMaxCount) {
        var self = this;
        var opt = self.options;
        var selectors = self.selectors;

        selectors.textAreaSelector = textAreaSelector;

        if (rowMaxSize) {
            opt.rowMaxSize = rowMaxSize;
        }

        if (rowMaxCount) {
            opt.rowMaxCount = rowMaxCount;
        }

        self.registerEvents();
    },

    registerEvents: function () {
        var self = this;
        var opt = self.options;
        var selectors = self.selectors;

        $(selectors.document).ready(function () {
            debugger;
            opt.textAreaCursorPosition = $(selectors.textAreaSelector).prop("selectionStart");
            var textAreaRowList = self.getTextAreaRowsList($(selectors.textAreaSelector).val());
            self.checkDefaultRowsSize(textAreaRowList);
        });

        $(selectors.textAreaSelector).keydown(function (event) {
            window.setTimeout(function () {
                opt.textAreaCursorPosition = parseInt($(selectors.textAreaSelector).prop("selectionStart"))-1;
                var textAreaRowList = self.getTextAreaRowsList($(selectors.textAreaSelector).val());
                self.checkChangedRowsSize(textAreaRowList);
            });
        });
    },

    getTextAreaRowsList: function () {
        var self = this;
        var opt = self.options;
        var selectors = self.selectors;

        var rowsList = $(selectors.textAreaSelector).val().split("\n");

        return rowsList;
    },

    checkDefaultRowsSize: function (rowsList) {
        var self = this;
        var opt = self.options;
        var selectors = self.selectors;

        $.each(rowsList, function (rowKey, row) {
            if (row.length > opt.rowMaxSize) {
                var def = row.length - opt.rowMaxSize;
                row = row.slice(0, -def);
            }
            rowsList[rowKey] = row;
        });

        opt.oldRowSize = rowsList;
        self.setCheckedRowList(rowsList);
    },

    checkChangedRowsSize: function (rowsList) {
        var self = this;
        var opt = self.options;
        var selectors = self.selectors;

        for (var i=0; i<rowsList.length; i++) {
            if (rowsList[i].length >= opt.rowMaxSize) {
                self.setCheckedRowList(opt.oldRowList);
                return false;
            }
        }

        checkedRowList = rowsList;
        opt.oldRowList = rowsList;
    },

    setCheckedRowList: function (checkedRowsList) {
        var self = this;
        var opt = self.options;
        var selectors = self.selectors;

        var checkedVal = '';
        $.each(checkedRowsList, function (rowKey, row) {
            if (rowKey < checkedRowsList.length - 1) {
                checkedVal = checkedVal + row + '\n';
            } else {
                checkedVal = checkedVal + row;
            }
        });

        $(selectors.textAreaSelector).focus().val(checkedVal).selectRange(opt.textAreaCursorPosition,0);
    },
}
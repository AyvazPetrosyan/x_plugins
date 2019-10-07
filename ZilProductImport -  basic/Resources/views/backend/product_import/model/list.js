Ext.define('Shopware.apps.ProductImport.model.List', {

    /**
     * Extends the standard Ext Model
     *
     * @string
     */
    extend: 'Ext.data.Model',

    /**
     * The fields used for this model
     *
     * @array
     */
    fields: [
        {
            name: "id",
            type: "int"
        },
        {
            name: "packageName",
            type: "string"
        },
        {
            name: "importDate",
            type: "string"
        },
        {
            name: "productCount",
            type: "int"
        },
        {
            name: "importedProductCount",
            type: "int"
        },
        {
            name: "updatedProductCount",
            type: "int"
        },
        {
            name: "state",
            type: "string"
        },
        {
            name: "packageImportDescription",
            type: "string"
        },
        {
            name: "status",
            type: "boolean"
        },
    ],

    /**
     * Configure the data communication
     *
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping for the different
         * store operations based on
         *
         * @object
         */
        api: {
            read: '{url  controller="ProductImport" action="packages"}'
        },

        /**
         * Configure the data reader
         *
         * @object
         */
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: "total"
        },

        success: function(response, opts) {
            //console.dir('fffffffffffff');
        },
    }
});
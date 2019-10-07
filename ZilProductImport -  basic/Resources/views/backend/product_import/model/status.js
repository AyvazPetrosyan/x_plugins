Ext.define('Shopware.apps.ProductImport.model.Status', {

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
            name: "mediaName",
            type: "string"
        },
        {
            name: "productNumber",
            type: "string"
        },
        {
            name: "result",
            type: "string"
        },
        {
            name: "importMessage",
            type: "string"
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
            read: '{url  controller="MediaSinc" action="mediaLoc"}'
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

        },
    },
});
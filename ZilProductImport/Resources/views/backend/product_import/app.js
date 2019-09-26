//{block name="backend/product_import/app"}
Ext.define('Shopware.apps.ProductImport', {

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name: 'Shopware.apps.ProductImport',

    /**
     * Extends from our special controller, which handles the
     * sub-application behavior and the event bus
     * @string
     */
    extend: 'Enlight.app.SubApplication',

    /**
     * Sets the loading path for the sub-application.
     *
     * Note that you'll need a "loadAction" in your
     * controller (server-side)
     * @string
     */
    loadPath: '{url action=load}',

    /**
     * load all files at once
     * @string
     */
    bulkLoad: true,

    /**
     * Required controllers
     * @array
     */
    controllers: [
        "Main"
    ],

    /**
     * Required views
     * @array
     */
    views: [
        "main.Window",
    ],

    /**
     * Required models
     * @array
     */
    models: [
        // "List"
    ],

    /**
     * Required stores
     * @array
     */
    stores: [
        // "List"
    ],

    launch: function () {
        var me = this,
            mainController = me.getController('Main');
        return mainController.mainWindow;
    }
});

//{/block}
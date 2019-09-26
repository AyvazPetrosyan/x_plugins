Ext.define('Shopware.apps.ProductImport.store.Status', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model: 'Shopware.apps.ProductImport.model.Status',
    remoteSort: false,
    remoteFilter: true,
    pageSize: 10,
});
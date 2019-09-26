Ext.define('Shopware.apps.ProductImport.store.List', {
    extend: 'Ext.data.Store',
    autoLoad: false,
    model: 'Shopware.apps.ProductImport.model.List',
    remoteSort: true,
    remoteFilter: true,
    pageSize: 10
});
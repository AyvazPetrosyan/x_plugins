//{namespace name=zill_product_import/backend/main}
Ext.define('Shopware.apps.ProductImport.controller.Main', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [

    ],
    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    snippets: {
        propertyWindowTitle: '{s name=PropertyWindowTitle}Property{/s}',
        statusWindowTitle: '{s name=StatusWindowTitle}Status{/s}',
    },

    init: function () {
        var me = this;
        var deleted = [];

        me.control({
            "package-main-window": {
                onOpenPropertiesDialog: function (record) {
                    me.closeWindow('property-window');
                    var propertyWindow = Ext.create('Ext.window.Window', {
                        id:'property-window',
                        title: me.snippets.propertyWindowTitle,
                        height: 200,
                        width: 400,
                        items: [
                            {
                                xtype: 'label',
                                text: "id: "+record.data.id+' ',
                            },
                            {
                                xtype: 'label',
                                forId: 'myFieldId',
                                text: "state: "+record.data.state+' ',
                            },
                            {
                                xtype: 'label',
                                forId: 'myFieldId',
                                text: "status: "+record.data.status+' ',
                            }
                        ]
                    }).show();


                },
                onCheckImportedDataStatus: function () {
                    me.closeWindow('status-window');
                    var store = me.getStore("Status");

                    store.load({
                        callback: function() {
                            var statusWindow = Ext.create('Ext.window.Window', {
                                id:'status-window',
                                title: me.snippets.statusWindowTitle,
                                height: 500,
                                width: 1000,
                                layout: 'fit',
                                items: [
                                    me.createStatusGrid(store),
                                ],
                                dockedItems: [{
                                    xtype: 'toolbar',
                                    dock: 'bottom',
                                    items: [
                                        {
                                            xtype: 'label',
                                            id: 'myStatus',
                                        }
                                    ]
                                }],
                            }).show();
                            me.getStatusInfo(store);
                        }
                    });
                }
            },
        });

        var store = me.getStore("List");

        store.load({
            callback: function () {
                me.closeWindow("package-main-window");
                me.mainWindow = me.getView('main.Window').create({
                    store: store
                });
                me.mainWindow.show();
            }
        });
        me.callParent(arguments);
    },

    createStatusGrid: function (store) {
        var me = this;
        return Ext.create('Ext.grid.Panel', {
            id: 'status-grid',
            alias: 'widget.status-grid',
            name: 'status-grid',
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'top',
                items: [{
                    xtype: 'tbfill'
                }, {
                    xtype: 'button',
                    cls: 'primary',
                    text: '{s name=UpdateStatus}Update status{/s}',
                    handler: function () {
                        me.getStatusInfo(store);
                    }
                }]
            }],
            store: store,
            columns: [
                {
                    xtype: 'rownumberer'
                },
                {
                    text: "{s name=ProductNumber}Product number{/s}",
                    dataIndex: 'productNumber',
                    flex: 1
                },
                {
                    text: "{s name=MediaName}Media name{/s}",
                    dataIndex: 'mediaName',
                    flex: 1
                },
                {
                    text: "{s name=ImportMessage}Import message{/s}",
                    dataIndex: 'importMessage',
                    flex: 1
                },
                {
                    text: "{s name=Result}Result{/s}",
                    dataIndex: 'result',
                    flex: 1,
                },
            ],
            width: '100%',
            height: '100%',
        });
    },

    getStatusInfo: function(store){
        var me = this;
        var sb = Ext.getCmp("myStatus");
        store.load({
            callback: function () {
                console.log(this);
                var succesCount = 0;
                var fayledCount = 0;
                var allMediaCount = 0;
                Ext.each(this.data.items, function (record) {
                    allMediaCount = record.raw.allMediaCount;
                    if(record.data.result == "success"){
                        succesCount+=1;
                    }
                    if(record.data.result == "failed"){
                        fayledCount+=1;
                    }
                });
                sb.setText("{s name=SuccessSync}Success synchronize media{/s}:"+succesCount+". {s name=FailedSync}Failed synchronize media{/s}:"+fayledCount+". {s name=AllMedia}All media{/s} ("+allMediaCount+")")
            }
        });
    },

    closeWindow: function (id) {
        if (Ext.getCmp(id)) {
            Ext.getCmp(id).close();
        }
    }
});
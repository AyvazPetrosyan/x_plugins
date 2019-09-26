//{namespace name=zill_product_import/backend/main}
Ext.define('Shopware.apps.ProductImport.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.package-main-window',
    title: '{s name=UploadedPackages}{/s}',
    layout: 'border',
    width: '95%',
    height: '95%',
    stateful: true,
    stateId: 'package-window',
    id: "package-main-window",
    mainWindow: null,


    snippets: {
        uploadedPackages:'{s name=UploadedPackages}Uploaded Packages{/s}',
        uploadFileLabelText:'{s name=UploadFile}Upload File{/s}',
        uploadFileButtonText:'{s name=SelectFile}Select File...{/s}',
        packagesGridTitle:'{s name=Packages}Packages{/s}',
        date:'{s name=Date}Date{/s}',
        size:'{s name=Size}Size{/s}',
        state:'{s name=State}State{/s}',
        status:'{s name=Status}Status{/s}',
        import:'{s name=Import}Import{/s}',
        syncMedia:'{s name=SyncMedia}Sync media{/s}',
    },

    initComponent: function () {
        let me = this;

        me.items = Ext.create('Ext.container.Container', {
            // layout: 'vbox',

            title: me.snippets.uploadedPackages,
            autoScroll: true,
            region: 'center',
            bodyPadding: 10,
            name: 'score-tab-panel',
            items: [
                {
                    xtype: 'form',
                    id: 'packagesForm',
                    flex: 11,
                    region: 'center',
                    width: '100%',
                    items: [{
                        xtype: 'filefield',
                        id: 'upload',
                        name: 'upload',
                        fieldLabel: me.snippets.uploadFileLabelText,
                        labelWidth: 100,
                        allowBlank: false,
                        anchor: '50%',
                        buttonText: me.snippets.uploadFileButtonText,
                    }],

                    buttons: [{
                        text: me.snippets.import,
                        cls: 'primary',
                        handler: function () {
                            setTimeout(Ext.bind(me.checkDownloadComplete, me), 500);
                        }
                    }, {
                        text: me.snippets.syncMedia,
                        cls: 'image-sync primary',
                        handler: function () {
                            let url = '{url  controller="MediaSinc" action="index" }';
                            let xhr = new XMLHttpRequest();

                            let formData = new FormData();
                            me.fireEvent('onCheckImportedDataStatus');
                            //this.disable(true);
                            Ext.Ajax.request({
                                url: url,
                                params: formData,
                                success: function (response) {
                                    if (response.success) {
                                        Shopware.Notification.createGrowlMessage(me.snippets.success, 'Media sync finished successfully.');
                                    } else {
                                        Shopware.Notification.createGrowlMessage(me.snippets.error, 'You can not import two or more media packages at the same time.');
                                    }
                                    //this.disable(false);
                                }
                            });
                        }
                    },
                        {
                            text: '{s name=SyncMediaStatus}Sync media status{/s}',
                            cls: 'image-sync primary',
                            handler: function () {
                                me.fireEvent('onCheckImportedDataStatus');
                            }
                        }]
                },
                me.createScoreGrid(),
            ]
        });
        me.callParent(arguments);
    },
    checkDownloadComplete: function () {
        let me = this;

        let form = Ext.getCmp('packagesForm').getForm();
        let url = '{url  controller="Upload" action="upload" }';
        let fileField = document.getElementsByName("upload")[0].files[0];


            if (form.isValid()) {
                me.setLoading(true);
                setTimeout(function () {
                    let xhr = new XMLHttpRequest();
                    xhr.open('post', url, false);
                    xhr.addEventListener('load', function (e) {
                        let target = e.target;
                        let response = Ext.decode(target.responseText);

                        me.setLoading(false);
                        if (response.success) {
                            Shopware.Notification.createGrowlMessage(me.snippets.success, response.message);
                        } else {
                            Shopware.Notification.createGrowlMessage(me.snippets.error, response.message);
                        }
                    }, false);
                    xhr.setRequestHeader('X-CSRF-Token', Ext.CSRFService.getToken());
                    let formData = new FormData();
                    formData.append('file', fileField);
                    xhr.send(formData);
                }, 500)
            }
    },

    createScoreGrid: function () {
        let me = this;

        return Ext.create('Ext.grid.Panel', {
            title: me.snippets.packagesGridTitle,
            id: 'score-grid',
            alias: 'widget.score-grid',
            name: 'score-grid',
            disabled: false,
            editable: true,
            sortableColumns: false,
            store: me.store,
            columns: [
                {
                    header: "Package name", //me.snippets.size
                    dataIndex: 'packageName',
                    flex: 1
                },
                {
                    header: "Import date", //me.snippets.size
                    dataIndex: 'importDate',
                    flex: 1
                },
                {
                    header: "All products count",
                    dataIndex: 'productCount',
                    flex: 1
                },
                {
                    header: "New products count",
                    dataIndex: 'importedProductCount',
                    flex: 1
                },
                {
                    header: "Updated products count",
                    dataIndex: 'updatedProductCount',
                    flex: 1
                },
                {
                    header: "Package state",
                    dataIndex: 'state',
                    flex: 1
                },
                {
                    header: "Package import message",
                    dataIndex: 'packageImportDescription',
                    flex: 1
                },

                {
                    xtype: 'booleancolumn',
                    header: me.snippets.status,
                    dataIndex: 'status',
                    width: 50,
                    style: { cursor:'pointer'},
                    trueText: '{literal}<div style="background: green; width: 100%; height: 13px"></div>{/literal}',
                    falseText: '{literal}<div style="background: red; width: 100%; height: 13px"></div>{/literal}',
                    listeners: {
                        click: function( element, v, b , c, k, record){
                            me.fireEvent('onOpenPropertiesDialog', record);
                        }
                    }
                }],
            width: '100%',
            height: '100%',
        });
    },
    createImportLoading: function () {

    }
});
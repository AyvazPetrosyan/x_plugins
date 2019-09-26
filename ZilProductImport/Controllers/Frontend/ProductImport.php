<?php

/**
 * Class Shopware_Controllers_Frontend_ProductImport
 */
class Shopware_Controllers_Frontend_ProductImport extends Enlight_Controller_Action {

    public function indexAction()
    {
        $this->Request();
    }

    public function removeAllProductsAction()
    {
        /** @var \ZilProductImport\Services\ProductImport\ProductImport $import */
        $import = $this->container->get('zil_product_import.services.ProductImport');
        $result = $import->removeProducts(null, true, true, true);

        $message = 'Failed';
        if ($result) {
            $message = 'Success';
        }

        $this->view->assign('message',$message);
    }

}
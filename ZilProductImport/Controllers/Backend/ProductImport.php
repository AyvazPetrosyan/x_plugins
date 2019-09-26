<?php

use ZilProductImport\Services\Package\Package;

/**
 * Class Shopware_Controllers_Backend_ProductImport
 */
class Shopware_Controllers_Backend_ProductImport extends Shopware_Controllers_Backend_ExtJs
{

    public function indexAction()
    {

    }

    public function packagesAction()
    {
        /** @var Package $packageService */
        $packageService = $this->container->get('zil_product_import.services.Package');
        /** @var \Shopware\Components\Model\ModelManager $model */
        $model = $this->container->get('models');
        $package = $model->getRepository(\ZilProductImport\Models\Package::class)->findAll();
        $packagesAssoc = $model->toArray($package);
        foreach ($packagesAssoc as $key=>$packageAssoc) {
            $packagesAssoc[$key]['status'] = false;
            /** @var DateTime $dateTime */
            if ($packageAssoc['state'] == $packageService::FINISHED) {
                $packagesAssoc[$key]['status'] = true;
            }
        }

        $this->view->assign('data',$packagesAssoc);
    }
}
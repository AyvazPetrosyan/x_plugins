<?php
/**
 * Created by PhpStorm.
 * User: Artyom
 * Date: 5/14/2019
 * Time: 2:36 PM
 */

class Shopware_Controllers_Backend_Packages extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @throws Exception
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $this->get("template")
            ->addTemplateDir($this->container->getParameter("zil_product_import.view_dir"));
    }

    public function indexAction()
    {
        parent::indexAction();
    }

    public function listingAction()
    {
        /** @var \Shopware\Components\Model\ModelManager $models */
        $models = $this->container->get('models');
        $result = $models->getRepository(\ZilProductImport\Models\Package::class)->findAll();
        $resultList = $models->toArray($result);

        $this->View()->assign('packageList', $resultList);
    }
}
<?php

namespace Test;

use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class Test
 * @package Test
 */
class Test extends Plugin
{
    const SNIPPETS_NAMESPACE = "test/backend/main";

    /**
     * @param InstallContext $installContext
     */
    public function install(InstallContext $installContext)
    {
        parent::install($installContext);
        $this->createAdditionalAttributes();
    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext)
    {
        parent::uninstall($uninstallContext);
        $this->removeAdditionalAttributes();
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function build(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->setParameter(
            $this->getContainerPrefix() . ".view_dir",
            $this->getPath() . "/Resources/views"
        );

        parent::build($containerBuilder);
    }

    private function createAdditionalAttributes()
    {
        /** @var Shopware_Components_Snippet_Manager $snippets */
        $snippets = $this->container->get('snippets');
        $snippetsNamespace = $snippets->getNamespace(self::SNIPPETS_NAMESPACE);

        /** @var CrudService $crudService */
        $crudService = $this->container->get("shopware_attribute.crud_service");

        $crudService->update('s_order_attributes', 'order_barcode', 'string', [
            'label' => $snippetsNamespace->get('barcodeLabel', 'Order barcode'),
            'supportText' => $snippetsNamespace->get('barcodeSupportText', 'Order barcode'),
            'helpText' => $snippetsNamespace->get('barcodeHelpText', 'Order barcode'),
            'translatable' => true,
            'displayInBackend' => false,
            'position' => 0,
            'custom' => false
        ]);

        /* create image attribute for category */
        $crudService->update("s_categories_attributes", "category_icon", TypeMapping::TYPE_SINGLE_SELECTION, [
            'label' => $snippetsNamespace->get('categoryIconLabel', 'Category icon'),
            'supportText' => $snippetsNamespace->get('categoryIconSupportText', 'Category icon'),
            'helpText' => $snippetsNamespace->get('categoryIconHelpText', 'Category icon'),
            'translatable' => true,
            'displayInBackend' => true,
            'entity' => 'Shopware\Models\Media\Media',
            'custom' => true
        ]);

        $this->rebuildAttributeModels();
    }

    private function removeAdditionalAttributes()
    {
        /** @var CrudService $crudService */
        $crudService = $this->container->get("shopware_attribute.crud_service");
        $crudService->delete('s_order_attributes', 'order_barcode');
        $this->rebuildAttributeModels();
    }

    public function rebuildAttributeModels()
    {
        $metaDataCache = $this->container->get('models')->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        $this->container->get('models')->generateAttributeModels([
            's_order_attributes'
        ]);
    }
}
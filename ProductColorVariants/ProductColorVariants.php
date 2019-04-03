<?php

namespace ProductColorVariants;

use Doctrine\ORM\Tools\SchemaTool;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
* Class ProductColorVariants
* @package ProductColorVariants
*/
class ProductColorVariants extends Plugin {

    /**
    * @param InstallContext $installContext
    */
    public function install(InstallContext $installContext) {
        parent::install($installContext);

        $this->createAdditionalAttributes();
    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext) {
        parent::uninstall($uninstallContext);

        $this->removeAdditionalAttributes();
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function build(ContainerBuilder $containerBuilder) {
        $containerBuilder->setParameter(
            $this->getContainerPrefix() . ".view_dir",
            $this->getPath() . "/Resources/views"
        );

        parent::build($containerBuilder);
    }

    private function createAdditionalAttributes() {
        /** @var CrudService $crudService */
        $crudService = $this->container->get("shopware_attribute.crud_service");
        $crudService->update('s_article_configurator_options_attributes', 'variants_color', 'string', [
            'label' => 'Color HEX code',
            'supportText' => 'Please type the color hex code.',
            'helpText' => 'Please type the color hex code.',
            'translatable' => true,
            'displayInBackend' => true,
            'position' => 0,
            'custom' => false
        ]);

        $this->rebuildAttributeModels();
    }

    private function removeAdditionalAttributes() {
        /** @var CrudService $crudService */
        $crudService = $this->container->get("shopware_attribute.crud_service");
        $crudService->delete('s_article_configurator_options_attributes', 'variants_color');
        $this->rebuildAttributeModels();
    }

    public function rebuildAttributeModels() {
        $metaDataCache = $this->container->get('models')->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();
        $this->container->get('models')->generateAttributeModels([
            's_article_configurator_options_attributes'
        ]);
    }

    private function createAdditionalModels() {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get("models");

        $schemaTool = new SchemaTool($modelManager);
        $schemaTool->createSchema([
            $modelManager->getClassMetadata()
        ]);
    }

    private function removeAdditionalModels() {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get("models");

        $schemaTool = new SchemaTool($modelManager);
        $schemaTool->dropSchema([
            $modelManager->getClassMetadata()
        ]);
    }
}
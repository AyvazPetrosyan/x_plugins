<?php

namespace ZilProductImport;

use
    Doctrine\ORM\Tools\SchemaTool;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Bundle\AttributeBundle\Service\TypeMapping;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Models\Attribute\Article;
use Shopware\Models\Tax\Tax;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ZilProductImport\Models\MediaLog;
use ZilProductImport\Models\MediaPackage;
use ZilProductImport\Models\Package;
use ZilProductImport\Models\PackageDetail;
use ZilProductImport\Models\Products;
use ZilProductImport\Models\ProductsLast;

/**
 * Class ZilProductImport
 * @package ZilProductImport
 */
class ZilProductImport extends Plugin
{
    const SNIPPETS_NAMESPACE = 'ZilProductImport';

    /**
     * @param InstallContext $installContext
     */
    public function install(InstallContext $installContext)
    {
        parent::install($installContext);

        $this->createAdditionalAttributes();
        $this->createAdditionalModels();
        $this->createTax();
        $this->createAdditionalColumns();
    }

    /**
     * @param UninstallContext $uninstallContext
     */
    public function uninstall(UninstallContext $uninstallContext)
    {
        parent::uninstall($uninstallContext);

        $this->removeAdditionalAttributes();
        $this->removeAdditionalModels();
    }

    /**
     * @param ContainerBuilder $containerBuilder
     */
    public function build(ContainerBuilder $containerBuilder)
    {
        $containerBuilder->setParameter(
            $this->getContainerPrefix() . '.view_dir',
            $this->getPath() . '/Resources/views'
        );

        parent::build($containerBuilder);
    }

    private function createAdditionalAttributes()
    {
        /** @var \Shopware_Components_Snippet_Manager $snippets */
        $snippets = $this->container->get('snippets');
        $snippetsNamespace = $snippets->getNamespace(self::SNIPPETS_NAMESPACE);
        /** @var CrudService $crudService */
        $crudService = $this->container->get('shopware_attribute.crud_service');

        if (!$crudService->get('s_articles_attributes', 'composure')) {
            $crudService->update('s_articles_attributes', 'composure', TypeMapping::TYPE_STRING, [
                    'label' => $snippetsNamespace->get('composureLabel', 'Composure'),
                    'supportText' => $snippetsNamespace->get('composureSupportText', 'Composure'),
                    'helpText' => $snippetsNamespace->get('composureHelpText', 'Composure'),
                    'translatable' => true,
                    'displayInBackend' => false,
                    'position' => 0,
                    'custom' => false
                ]
            );
        }
        if (!$crudService->get('s_articles_attributes', 'is_online_shop')) {
            $crudService->update('s_articles_attributes', 'is_online_shop', TypeMapping::TYPE_BOOLEAN, [
                'label' => $snippetsNamespace->get('isOnlineShopLabel', 'Is online shop'),
                'supportText' => $snippetsNamespace->get('isOnlineShopSupportText', 'Is online shop'),
                'helpText' => $snippetsNamespace->get('isOnlineShopHelpText', 'Is online shop'),
                'translatable' => true,
                'displayInBackend' => true,
                'position' => 0,
                'custom' => false
            ], null, false, 0);
        }
        if (!$crudService->get('s_core_shops_attributes', 'is_product_import')) {
            $crudService->update('s_core_shops_attributes', 'is_product_import', TypeMapping::TYPE_BOOLEAN, [
                'label' => $snippetsNamespace->get('isProductImportLabel', 'IS product import'),
                'supportText' => $snippetsNamespace->get('isProductImportSupportText', 'Is product import'),
                'helpText' => $snippetsNamespace->get('isProductImportHelpText', 'Is product import'),
                'translatable' => true,
                'displayInBackend' => true,
                'position' => 0,
                'custom' => false
            ], null, false, 0);
        }
        if (!$crudService->get('s_articles_attributes', 'is_imported')) {
            $crudService->update('s_articles_attributes', 'is_imported', TypeMapping::TYPE_BOOLEAN, [
                'displayInBackend' => false,
                'custom' => false
            ], null, false, 0);
        }

        $this->generateAttributeModels();
    }

    private function removeAdditionalAttributes()
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get('models');
        /** @var CrudService $crudService */
        $crudService = $this->container->get('shopware_attribute.crud_service');

        if ($crudService->get('s_articles_attributes', 'composure')) {
            $crudService->delete('s_articles_attributes', 'composure');
        }
        if ($crudService->get('s_articles_attributes', 'is_imported')) {
            $crudService->delete('s_articles_attributes', 'is_imported');
        }
        if ($crudService->get('s_core_shops_attributes', 'is_product_import')) {
            $crudService->delete('s_core_shops_attributes', 'is_product_import');
        }

        $isZilMenuAdditionalActive = true;
        /** @var \Shopware\Models\Plugin\Plugin $pluginModel */
        $zilMenuAdditionalModel = $modelManager->getRepository(\Shopware\Models\Plugin\Plugin::class)->findOneBy(['name' => 'ZilMenuAdditional']);
        if ($zilMenuAdditionalModel) {
            $isZilMenuAdditionalActive = $zilMenuAdditionalModel->getActive();
        }
        $attrExist = $crudService->get('s_articles_attributes', 'is_online_shop');
        if ($attrExist && !$isZilMenuAdditionalActive) {
            $crudService->delete('s_articles_attributes', 'is_online_shop');
        }
        $this->generateAttributeModels();
    }

    private function generateAttributeModels()
    {
        $metaDataCache = $this->container->get('models')->getConfiguration()->getMetadataCacheImpl();
        $metaDataCache->deleteAll();

        $this->container->get('models')->generateAttributeModels([
            's_articles_attributes',
            's_core_shops_attributes'
        ]);
    }

    private function createAdditionalModels()
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get('models');

        try {
            $schemaTool = new SchemaTool($modelManager);
            $schemaTool->createSchema([
                $modelManager->getClassMetadata(Products::class),
                $modelManager->getClassMetadata(ProductsLast::class),
                $modelManager->getClassMetadata(Package::class),
                $modelManager->getClassMetadata(PackageDetail::class),
                $modelManager->getClassMetadata(MediaPackage::class),
                $modelManager->getClassMetadata(MediaLog::class)
            ]);
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
        }
    }

    private function removeAdditionalModels()
    {
        /** @var ModelManager $modelManager */
        $modelManager = $this->container->get('models');

        try {
            $schemaTool = new SchemaTool($modelManager);
            $schemaTool->dropSchema([
                $modelManager->getClassMetadata(Products::class),
                $modelManager->getClassMetadata(ProductsLast::class),
                $modelManager->getClassMetadata(Package::class),
                $modelManager->getClassMetadata(PackageDetail::class),
                $modelManager->getClassMetadata(MediaPackage::class),
                $modelManager->getClassMetadata(MediaLog::class)
            ]);
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
        }
    }

    private function createTax()
    {
        /** @var ModelManager $model */
        $model = $this->container->get('models');
        $result = $model->getRepository(Tax::class)->findOneBy(['tax' => 0]);

        if (empty($result)) {
            $tax = new Tax();
            $tax->setTax(0);
            $tax->setName('0%');

            $model->persist($tax);
            $model->flush();
        }
    }

    private function createAdditionalColumns()
    {
        $dbConfigs = Shopware()->DB()->getConfig();
        $dbName = $dbConfigs['dbname'];
        $columnsList = Shopware()->Db()->fetchAssoc(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N's_media' AND TABLE_SCHEMA=?",
            [$dbName]
        );
        if(!array_key_exists('is_imported', $columnsList)) {
            Shopware()->Db()->query('ALTER TABLE s_media ADD COLUMN is_imported BOOL');
            Shopware()->Db()->query('UPDATE `s_media` SET is_imported=0');
        }
    }

    private function removeAdditionalColumns()
    {
        $dbConfigs = Shopware()->DB()->getConfig();
        $dbName = $dbConfigs['dbname'];
        $columnsList = Shopware()->Db()->fetchAssoc(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = N's_media' AND TABLE_SCHEMA=?",
            [$dbName]
        );
        if(array_key_exists('is_imported', $columnsList)) {
            Shopware()->Db()->query('ALTER TABLE s_media DROP COLUMN is_imported');
        }
    }
}
<?php

namespace ZilProductImport\Services\Package;

use Shopware\Components\Model\ModelManager;
use ZilProductImport\Models\Package as PackageModel;
use ZilProductImport\Models\PackageDetail;

class Package
{
    const IN_PROCESS = 'In process';

    const FINISHED = 'Finished';

    const FAILED = 'Failed';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function insertPackage($packageName = null)
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        /** @var ModelManager $model */
        $model = $this->container->get('models');
        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");

        $importDate = date('Y-m-d h:i:sa');
        try {
            $package = new PackageModel();
            $package->setPackageName($packageName);
            $package->setPackageName($packageName);
            $package->setImportDate($importDate);
            $package->setState(self::IN_PROCESS);
            $package->setProductCount(0);
            $package->setImportedProductCount(0);
            $package->setUpdatedProductCount(0);
            $package->setPackageImportDescription('');
            $model->persist($package);
            $model->flush();
            $result['package'] = $package;
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
            $result = [
                'message' => $snippetManager->get('ImportFailed'),
                'result' => false
            ];
        }

        return $result;
    }

    public function updatePackageInfo($packageName, $packageState = null, $importDescription = null)
    {
        $result = [
            'message' => '',
            'result' => true
        ];

        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");
        /** @var ModelManager $model */
        $model = $this->container->get('models');
        /** @var PackageModel $package */
        $packageModel = $model->getRepository(PackageModel::class)->findOneBy(['state' => self::IN_PROCESS]);

        if($packageModel) {
            try {
                $productCount = (int)Shopware()->Db()->fetchOne("SELECT COUNT(*) FROM products");
                $newProductCount = (int)Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM products WHERE product_status = \'new\'');
                $updatedProductCount = (int)Shopware()->Db()->fetchOne('SELECT COUNT(*) FROM products WHERE product_status = \'updated\'');

                $packageModel->setState($packageState);
                $packageModel->setPackageName($packageName);
                $packageModel->setProductCount($productCount);
                $packageModel->setImportedProductCount($newProductCount);
                $packageModel->setUpdatedProductCount($updatedProductCount);
                $packageModel->setPackageImportDescription($importDescription);
                $model->persist($packageModel);
                $model->flush();
            } catch (\Exception $e) {
                $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
                $result = [
                    'message' => $snippetManager->get('ImportFailed'),
                    'result' => false
                ];
            }
        }

        /*
         * Could not change this column value with repository object
         * because it`s gives this error message
         * '$model object already closed'
        */
        if ($packageState == self::FAILED) {
            Shopware()->Db()->query('UPDATE `package` SET package_state = \'failed\' WHERE package_state=?',[
                self::IN_PROCESS
            ]);
        }

        return $result;
    }

    public function insertPackageLog($packageLog)
    {
        /** @var ModelManager $model */
        $model = $this->container->get('models');

        try {
            /** @var PackageDetail $package */
            $packageDetailsModel = new PackageDetail();
            $packageDetailsModel->setPackageId($packageLog['packageId']);
            $packageDetailsModel->setImportState($packageLog['importState']);
            $model->persist($packageDetailsModel);
            $model->flush();
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
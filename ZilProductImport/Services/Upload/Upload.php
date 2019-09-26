<?php

namespace ZilProductImport\Services\Upload;

use Psr\Container\ContainerInterface;
use ZilProductImport\Services\Compare\Compare;
use ZilProductImport\Services\InsertData\Insert;
use ZilProductImport\Services\Package\Package;
use ZilProductImport\Services\ProductImport\ProductImport;
use ZilProductImport\Services\Validation\CsvValidationService;
use ZilProductImport\Services\Validation\PackageValidation;

class Upload
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function upload($packageName, $filesList)
    {
        $packageModel = null;
        $packageId = null;
        set_time_limit(0);
        $time_pre_0 = microtime(true);

        /** @var PackageValidation $packageValidation */
        $packageValidation = $this->container->get('zil_product_import.services.PackageValidation');
        $packageValidationResult = $packageValidation->packageValidation();
        if (!$packageValidationResult['result']) {
            return $packageValidationResult;
        }

        /** @var Package $package */
        $package = $this->container->get('zil_product_import.services.Package');
        $packageResult = $package->insertPackage($packageName);
        if ($packageResult['result']) {
            $time_post_1 = microtime(true);
            $def1 = $time_post_1-$time_pre_0;

            /** @var \ZilProductImport\Models\Package $packageModel */
            $packageModel = $packageResult['package'];
            $packageId = $packageModel->getId();
            $packageLog['packageId'] = $packageId;
            $packageLog['importState'] = 'The package inserted successfully: duration - ' . $def1 . ' seconds';
            if (is_null($packageModel) || is_null($packageId)){
                return false;
            }
            $package->insertPackageLog($packageLog);
        } else {
            return $packageResult;
        }

        /** @var CsvValidationService $csvValidationService */
        $csvValidationService = $this->container->get('zil_product_import.services.CsvValidationService');
        $validationResult = $csvValidationService->validation();
        if ($validationResult['result']) {
            $time_post_2 = microtime(true);
            $def2 = $time_post_2-$time_post_1;

            $packageLog['packageId'] = $packageId;
            $packageLog['importState'] = 'The csv file validated successfully: duration - ' . $def2 . ' seconds';
            $package->insertPackageLog($packageLog);
        } else {
            $package->updatePackageInfo($packageName, $package::FAILED, $validationResult['message']);
            return $validationResult;
        }

        /** @var Insert $insert */
        $insert = $this->container->get('zil_product_import.services.Insert');
        $insertResult = $insert->insertUploadedFiles($filesList);
        if ($insertResult['result']) {
            $time_post_3 = microtime(true);
            $def3 = $time_post_3-$time_post_2;

            $packageLog['packageId'] = $packageId;
            $packageLog['importState'] = 'The uploaded files inserted successfully: duration - ' . $def3 . ' seconds';
            $package->insertPackageLog($packageLog);
        } else {
            $package->updatePackageInfo($packageName, $package::FAILED, $insertResult['message']);
            return $insertResult;
        }

        /** @var Compare $compare */
        $compare = $this->container->get('zil_product_import.services.Compare');
        $compareResult = $compare->compare('products_last', 'products');
        if ($compareResult['result']) {
            $time_post_4 = microtime(true);
            $def4 = $time_post_4-$time_post_3;

            $packageLog['packageId'] = $packageId;
            $packageLog['importState'] = 'The uploaded products compared successfully: duration - ' . $def4 . ' seconds';
            $package->insertPackageLog($packageLog);
        } else {
            $package->updatePackageInfo($packageName, $package::FAILED, $compareResult['message']);
            return $compareResult;
        }

        /** @var ProductImport $import */
        $import = $this->container->get('zil_product_import.services.ProductImport');
        $importResult = $import->importProducts($packageId);
        if ($importResult['result']) {
            $time_post_5 = microtime(true);
            $def5 = $time_post_5-$time_post_4;

            $packageLog['packageId'] = $packageId;
            $packageLog['importState'] = 'The uploaded products imported successfully: duration - ' . $def5 . ' seconds';
            $package->insertPackageLog($packageLog);
        } else {
            $package->updatePackageInfo($packageName, $package::FAILED, $importResult['message']);
        }

        return $importResult;
    }
}
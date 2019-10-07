<?php

namespace ZilProductImport\Services\Compare;

use Psr\Container\ContainerInterface;
use Shopware\Components\Model\ModelManager;
use ZilProductImport\Models\Products;
use ZilProductImport\Services\Files\FilesService;

class Compare
{
    private $container;

    private $previousTable;

    private $newTable;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function compare($previousTable, $newTable)
    {
        $result = [
            'errorMessage' => 'Successfully compared',
            'result' => true
        ];

        $this->previousTable = $previousTable;
        $this->newTable = $newTable;
        /** @var FilesService $filesService */
        $filesService = $this->container->get('zil_product_import.services.FilesService');

        $newProductsId = $this->getNewProducts($previousTable, $newTable);
        $setNew = $this->setStatus($newProductsId, $filesService::NEW_PRODUCT_STATUS);
        if (!$setNew) {
            $result = [
                'errorMessage' => 'Compare failed',
                'result' => false
            ];
        }

        $updatedProductsId = $this->getUpdatedProducts($previousTable, $newTable);
        $setUpdate = $this->setStatus($updatedProductsId, $filesService::UPDATED_PRODUCT_STATUS);
        if (!$setUpdate) {
            $result = [
                'errorMessage' => 'Compare failed',
                'result' => false
            ];
        }

        return $result;
    }

    private function setStatus($productsId, $status)
    {
        if (empty($status)) {
            return false;
        }

        try {
            /** @var ModelManager $models */
            $models = $this->container->get('models');
            foreach ($productsId as $productKey => $productId) {
                $result = $models->find(Products::class, $productId);
                $result->setProductStatus($status);
                $models->persist($result);
            }
            $models->flush();
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport '.$e->getMessage());
            return false;
        }

        return true;
    }

    private function getNewProducts($previousTable, $newTable)
    {
        $result = [];

        try {
            $sql = "SELECT $newTable.id
                FROM $newTable LEFT JOIN $previousTable
                ON $newTable.product_number = $previousTable.product_number
                WHERE $previousTable.id IS NULL ";
            $resultsInfo = Shopware()->Db()->fetchAssoc($sql);
            foreach ($resultsInfo as $resultKey => $resultInfo) {
                $result[] = $resultInfo['id'];
            }
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport '.$e->getMessage());
            return false;
        }

        return $result;
    }

    private function getUpdatedProducts($previousTable, $newTable)
    {
        try {
            $sql = "SELECT $newTable.id FROM $newTable
                LEFT JOIN $previousTable
                ON $newTable.hash = $previousTable.hash
                WHERE $previousTable.id IS NULL 
                AND $newTable.product_status IS NULL";
            $resultsInfo = Shopware()->Db()->fetchAssoc($sql);
            foreach ($resultsInfo as $resultKey => $resultInfo) {
                $result[] = $resultInfo['id'];
            }
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport '.$e->getMessage());
            return false;
        }

        return $result;
    }
}
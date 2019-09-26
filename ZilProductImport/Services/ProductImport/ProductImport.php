<?php

namespace ZilProductImport\Services\ProductImport;

use Psr\Container\ContainerInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article as Article;
use Shopware\Models\Article\Configurator\Option;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Detail as ArticleDetail;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier as Supplier;
use Shopware\Models\Attribute\Article as ArticleAttr;
use ZilProductImport\Models\PackageDetail;
use ZilProductImport\Services\Category\Category as CategoryService;
use Shopware\Models\Customer\Group;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Tax\Tax;
use ZilProductImport\Models\Products;
use ZilProductImport\Services\Files\FilesService;

class ProductImport
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var ModelManager
     */
    private $model;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $snippetManager;

    private $result = [];

    private $packageId;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->model = $container->get('models');
        $this->snippetManager = $container->get("snippets")->getNamespace("zill_product_import/backend/main");
    }

    public function importProducts($packageId = null)
    {
        $this->result = [
            'message' => 'Successfully imported',
            'result' => true
        ];

        $this->packageId = $packageId;

        $productInfoList = $this->getUploadedProducts();
        $newProductList = $productInfoList['newProducts'];
        $changedProductList = $productInfoList['updatedProducts'];

        $this->createProduct($newProductList);
        $this->updateProduct($changedProductList);

        return $this->result;
    }

    public function removeProducts($productsId, $removeAll = false, $removeCategories = false, $removePrice = false)
    {
        Shopware()->Db()->query("DELETE FROM products");
        Shopware()->Db()->query("DELETE FROM products_last");
        Shopware()->Db()->query("DELETE FROM package");
        Shopware()->Db()->query("DELETE FROM package_detail");

        if ($removeCategories) {
            Shopware()->Db()->query("DELETE FROM s_categories WHERE parent != 1");
            Shopware()->Db()->query("DELETE FROM s_categories_attributes");
        }

        if ($removeAll) {
            Shopware()->Db()->query("DELETE FROM s_articles");
            Shopware()->Db()->query("DELETE FROM s_articles_details");
            Shopware()->Db()->query("DELETE FROM s_articles_attributes");
            Shopware()->Db()->query("DELETE FROM s_articles_categories");
            Shopware()->Db()->query("DELETE FROM s_articles_categories_ro");
            Shopware()->Db()->query("DELETE FROM s_article_configurator_option_relations");
            Shopware()->Db()->query("DELETE FROM s_articles_img");
            if ($removePrice) {
                Shopware()->Db()->query("DELETE FROM s_articles_prices");
            }

            return true;
        }

        foreach ($productsId as $productId) {
            Shopware()->Db()->query("DELETE FROM s_articles WHERE articleId = ?", [$productId]);
            Shopware()->Db()->query("DELETE FROM s_articles_details WHERE articleId = ?", [$productId]);
            Shopware()->Db()->query("DELETE FROM s_articles_attributes WHERE articleId = ?", [$productId]);
            Shopware()->Db()->query("DELETE FROM s_articles_categories WHERE articleId = ?", [$productId]);
            Shopware()->Db()->query("DELETE FROM s_articles_categories_ro WHERE articleId = ?", [$productId]);
            if ($removePrice) {
                Shopware()->Db()->query("DELETE FROM s_articles_prices WHERE articleID = ?", [$productId]);
            }
        }

        return true;
    }

    private function createProduct($newProductList)
    {
        /*
         * + parent_number
         * + name
         * + description
         * + composure
         * - image
         * + size
         * + color
         * + 'productNumber'    required value
         * + active' => '1'     required value
         * + manufacturer       required value
         * + stock              required value
         * + lastStock          required value
         * + first_price
         * + last_price         required value
         * + categories         required value
         * + kind               required value
         * + taxValue           required value
         *
         * */
        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");
        /** @var FilesService $fileService */
        $fileService = $this->container->get('zil_product_import.services.FilesService');
        /** @var CategoryService $categoryService */
        $categoryService = $this->container->get('zil_product_import.services.Category');

        $importInfo = [];
        $importInfo['packageId'] = $this->packageId;

        try {
            foreach ($newProductList as $productKey => $productInfo) {
                if (empty($productInfo['lastStock'])) {
                    $productInfo['lastStock'] = 1;
                }
                if (empty($productInfo['taxValue'])) {
                    $productInfo['taxValue'] = 0;
                }

                $fieldsName = $fileService->getProductsFieldsNameList();

                $productInfo['customerGroup'] = $this->getCustomerGroup();
                $productInfo['supplier'] = $this->getManufacturer($productInfo['manufacturer']);
                $productInfo['tax'] = $this->model->getRepository(Tax::class)->findOneBy(['tax' => $productInfo['taxValue']]);
                $productInfo['colorOption'] = $this->getOption($productInfo['color'], $fieldsName['color']['name']);
                $productInfo['sizeOption'] = $this->getOption($productInfo['size'], $fieldsName['size']['name']);
                $productInfo['configuratorSet'] = $this->getConfiguratorSet($productInfo);
                $productInfo['categoriesModel'] = $categoryService->getCategoriesModels($productInfo['categories']);
                $productInfo['product'] = $this->importArticle($productInfo);
                $productInfo['detail'] = $this->importDetail($productInfo);
                $this->importArticleAttr($productInfo);
                $this->productPriceImport($productInfo);

                $importInfo['productNumber'] = $productInfo['productNumber'];
                $importInfo['productImportDescription'] = 'Imported successfully';
                $importInfo['importState'] = $fileService::PRODUCT_IMPORT_STAT;
                $this->setProductImportLog($importInfo);

                $this->model->clear();
            }
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport product number ' . $productInfo['productNumber'] . ' ' . $e->getMessage());

            $importInfo['productNumber'] = $productInfo['productNumber'];
            $importInfo['productImportDescription'] = 'Filed';
            $this->setProductImportLog($importInfo);
        }
    }

    private function updateProduct($changedProductList)
    {
        /*
         * - parent_number
         * + name
         * + description
         * + composure
         * + manufacturer
         * + categories
         * - image
         * - size
         * - color
         * + stock
         * + active
         * + first_price
         * + last_price
         * + is_main
         * */
        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");
        /** @var FilesService $fileService */
        $fileService = $this->container->get('zil_product_import.services.FilesService');
        /** @var CategoryService $categoryService */
        $categoryService = $this->container->get('zil_product_import.services.Category');

        $importInfo = [];
        $importInfo['packageId'] = $this->packageId;

        if (!empty($changedProductList)) {
            try {
                foreach ($changedProductList as $changedProduct => $changedProductInfo) {
                    if (empty($changedProductInfo['lastStock'])) {
                        $changedProductInfo['lastStock'] = 1;
                    }
                    if (empty($changedProductInfo['taxValue'])) {
                        $changedProductInfo['taxValue'] = 0;
                    }

                    $fieldsName = $fileService->getProductsFieldsNameList();

                    $changedProductInfo['customerGroup'] = $this->getCustomerGroup();
                    $changedProductInfo['supplier'] = $this->getManufacturer($changedProductInfo['manufacturer']);
                    $changedProductInfo['tax'] = $this->model->getRepository(Tax::class)->findOneBy(['tax' => $changedProductInfo['taxValue']]);
                    $changedProductInfo['colorOption'] = $this->getOption($changedProductInfo['color'], $fieldsName['color']['name']);
                    $changedProductInfo['sizeOption'] = $this->getOption($changedProductInfo['size'], $fieldsName['size']['name']);
                    $changedProductInfo['configuratorSet'] = $this->getConfiguratorSet($changedProductInfo);
                    $changedProductInfo['categoriesModel'] = $categoryService->getCategoriesModels($changedProductInfo['categories']);
                    $changedProductInfo['product'] = $this->importArticle($changedProductInfo);
                    $changedProductInfo['detail'] = $this->importDetail($changedProductInfo);
                    $this->importArticleAttr($changedProductInfo);
                    $this->productPriceImport($changedProductInfo);

                    $importInfo['productNumber'] = $changedProductInfo['productNumber'];
                    $importInfo['productImportDescription'] = 'Updated successfully';
                    $importInfo['importState'] = $fileService::PRODUCT_UPDATE_STATE;
                    $this->setProductImportLog($importInfo);

                    $this->model->clear();
                }
            } catch (\Exception $e) {
                $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());

                $importInfo['productNumber'] = $changedProductInfo['productNumber'];
                $importInfo['productImportDescription'] = 'Filed';
                $this->setProductImportLog($importInfo);
            }
        }
    }

    private function setProductImportLog($importInfo)
    {
        $packageDetail = new PackageDetail();
        $packageDetail->setPackageId($importInfo['packageId']);
        $packageDetail->setProductNumber($importInfo['productNumber']);
        $packageDetail->setProductImportDescription($importInfo['productImportDescription']);
        $packageDetail->setImportState($importInfo['importState']);

        $this->model->persist($packageDetail);
        $this->model->flush();
    }

    private function getUploadedProducts()
    {
        /** @var FilesService $filesService */
        $filesService = $this->container->get('zil_product_import.services.FilesService');
        $uploadedProducts = [];
        $uploadedProducts['newProducts'] = $this->model->toArray($this->model->getRepository(Products::class)->findBy(['productStatus' => $filesService::NEW_PRODUCT_STATUS]));
        $uploadedProducts['updatedProducts'] = $this->model->toArray($this->model->getRepository(Products::class)->findBy(['productStatus' => $filesService::UPDATED_PRODUCT_STATUS]));

        return $uploadedProducts;
    }

    private function getOption($optionName, $groupName)
    {
        if (empty($groupName)){
            return false;
        }
        /** @var \Shopware\Models\Article\Configurator\Group $optionGroup */
        $optionGroup = $this->model->getRepository(ConfiguratorGroup::class)->findOneBy(['name' => $groupName]);
        if (empty($optionGroup)) {
            $optionGroup = new ConfiguratorGroup();
            $optionGroup->setName($groupName);
            $optionGroup->setPosition(1);
            $this->model->persist($optionGroup);
            $this->model->flush();
        }

        if (empty($optionName)){
            return false;
        }
        /** @var Option $option */
        $option = $this->model->getRepository(Option::class)->findOneBy(['name' => $optionName]);
        if (empty($option)) {
            $option = new Option();
            $option->setName($optionName);
            $option->setPosition(1);
            $option->setGroup($optionGroup);
            $this->model->persist($option);
            $this->model->flush();
        }

        return $option;
    }

    private function getConfiguratorSet($productInfo)
    {
        $groups = [];
        $options[] = $productInfo['colorOption'];
        $options[] = $productInfo['sizeOption'];
        if(empty($options)){
            return false;
        }
        if ($productInfo['colorOption']) {
            $groups[] = $productInfo['colorOption']->getGroup();
        }
        if ($productInfo['sizeOption']) {
            $groups[] = $productInfo['sizeOption']->getGroup();
        }
        $configuratorSetName = "Set-" . $productInfo['parentNumber'];

        if (!$productInfo['isMain']){
            /** @var ArticleDetail $detail */
            $detail = $this->model->getRepository(Detail::class)->findOneBy(['number'=>$productInfo['parentNumber']]);
            foreach ($detail->getConfiguratorOptions()->getValues() as $cOptionKey=>$cOption){
                $options[] = $cOption;
            }
        }

        try {
            /** @var Set $configuratorSet */
            $configuratorSet = $this->model->getRepository(Set::class)->findOneBy(['name' => $configuratorSetName]);
            if (empty($configuratorSet)) {
                $configuratorSet = new Set();
            }
            $configuratorSet->setName($configuratorSetName);
            $configuratorSet->setOptions($options);
            $configuratorSet->setGroups($groups);
            $this->model->persist($configuratorSet);
            $this->model->flush();
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
        }

        return $configuratorSet;
    }

    private function getManufacturer($manufacturerName)
    {
        /** @var Supplier $supplier */
        $manufacturerModel = $this->model->getRepository(Supplier::class)->findOneBy(['name' => $manufacturerName]);
        if (empty($manufacturerModel)) {
            $manufacturerModel = new Supplier();
            $manufacturerModel->setName($manufacturerName);
            $manufacturerModel->setImage('no image');
            $manufacturerModel->setChanged();
            $this->model->persist($manufacturerModel);
            $this->model->flush();
        }

        return $manufacturerModel;
    }

    private function importArticle($productInfo)
    {
        if (!$productInfo['isMain']) {
            return false;
        }

        $productDetail = $this->model->getRepository(ArticleDetail::class)->findOneBy(['number' => $productInfo['productNumber']]);
        if (empty($productDetail)) {
            /** @var Detail $productDetail */
            $productDetail = new ArticleDetail();
            /** @var Article $product */
            $product = new Article();
        } else {
            $productId = $productDetail->getArticleId(); //$this->model->toArray($productDetail)['articleId'];
            $product = $this->model->getRepository(Article::class)->find($productId);
        }

        if (!$productDetail || !$product) {
            return false;
        }
        $productOptions = [];
        if (!empty($productInfo['colorOption'])) {
            $productOptions[] = $productInfo['colorOption'];
        }
        if (!empty($productInfo['sizeOption'])) {
            $productOptions[] = $productInfo['sizeOption'];
        }

        $result = [];

        $product->setPropertyValues();
        $product->setName($productInfo['name']);
        $product->setActive($productInfo['active']);
        $product->setDescription($productInfo['description']);
        $product->setSupplier($productInfo['supplier']);
        $product->setTax($productInfo['tax']);
        $product->setLastStock($productInfo['lastStock']);
        $product->setCategories($productInfo['categoriesModel']);
        if($productInfo['configuratorSet']) {
            $product->setConfiguratorSet($productInfo['configuratorSet']);
        }
        $this->model->persist($product);
        $this->model->flush();

        $productDetail->setNumber($productInfo['productNumber']);
        $productDetail->setKind($productInfo['isMain']);
        $productDetail->setInStock($productInfo['stock']);
        $productDetail->setLastStock($productInfo['lastStock']);
        $productDetail->setActive($productInfo['active']);
        if ($productOptions) {
            $productDetail->setConfiguratorOptions($productOptions);
        }
        $productDetail->setArticle($product);
        $this->model->persist($productDetail);
        $this->model->flush();

        $product->setMainDetail($productDetail);
        $this->model->persist($product);
        $this->model->flush();

        $result['product'] = $product;
        $result['mainDetail'] = $productDetail;

        return $result;
    }

    private function importDetail($productInfo)
    {
        if ($productInfo['isMain']) {
            return false;
        }

        /** @var ArticleDetail $mainDetail */
        $mainDetail = $this->model->getRepository(Detail::class)->findOneBy(['number' => $productInfo['parentNumber']]);
        $productId = $mainDetail->getArticleId();
        /** @var Article $product */
        $product = $this->model->getRepository(Article::class)->findOneBy(['id'=>$productId]);

        if (!$product) {
            return false;
        }
        $productOptions = [];
        if (!empty($productInfo['colorOption'])) {
            $productOptions[] = $productInfo['colorOption'];
        }
        if (!empty($productInfo['sizeOption'])) {
            $productOptions[] = $productInfo['sizeOption'];
        }

        /** @var ArticleDetail $productDetail */
        $productDetail = $this->model->getRepository(Detail::class)->findOneBy(['number' => $productInfo['productNumber']]);
        if (empty($productDetail)) {
            $productDetail = new ArticleDetail();
        }
        $productDetail->setNumber($productInfo['productNumber']);
        $productDetail->setKind(2);
        $productDetail->setInStock($productInfo['stock']);
        $productDetail->setLastStock($productInfo['lastStock']);
        $productDetail->setActive($productInfo['active']);
        if ($productOptions) {
            $productDetail->setConfiguratorOptions($productOptions);
        }
        $productDetail->setArticle($product);
        $this->model->persist($productDetail);
        $this->model->flush();

        return $productDetail;
    }

    private function importArticleAttr($productInfo)
    {
        if (!$productInfo['detail']) {
            $productInfo['detail'] = $productInfo['product']['mainDetail'];
        }
        /** @var ArticleAttr $productAttr */
        $productAttr = $this->model->getRepository(ArticleAttr::class)->findOneBy(['articleDetailId'=>$productInfo['detail']]);
        if (empty($productAttr)){
            $productAttr = new ArticleAttr();
        }
        $productAttr->setArticleDetail($productInfo['detail']);
        $productAttr->setIsImported(1);
        $productAttr->setIsOnlineShop($productInfo['isOnlineShop']);
        $this->model->persist($productAttr);
        $this->model->flush();

        return $productAttr;
    }

    private function productPriceImport($productInfo)
    {
        if ($productInfo['isMain']) {
            /** @var Article $productModel */
            $productModel = $productInfo['product']['product'];
            /** @var Detail $detailModel */
            $detailModel = $productInfo['product']['mainDetail'];
        } else {
            /** @var Detail $detailModel */
            $detailModel = $productInfo['detail'];
            $productModel = $detailModel->getArticle();
        }
        $productPrice = $this->model->getRepository(Price::class)->findOneBy(['articleDetailsId' => $detailModel->getId()]);

        if (empty($productPrice)) {
            /** @var Price $productPrice */
            $productPrice = new Price();
        }
        $productPrice->setFrom(1);
        $productPrice->setTo('beliebig');
        $productPrice->setPrice($productInfo['lastPrice']);
        $productPrice->setPseudoPrice($productInfo['firstPrice']);
        $productPrice->setCustomerGroup($productInfo['customerGroup']);
        $productPrice->setArticle($productModel);
        $productPrice->setDetail($detailModel);
        $this->model->persist($productPrice);
        $this->model->flush();

        return $productPrice;
    }

    private function getCustomerGroup()
    {
        $customerGroup = $this->model->getRepository(Group::class)->findOneBy(['key' => 'EK']);
        if (empty($customerGroup)) {
            $customerGroup = new Group();
            $customerGroup->setName('Default');
            $customerGroup->setKey('EK');
            $customerGroup->setTax(0);
            $customerGroup->setTaxInput(0);
            $customerGroup->setMode(0);
            $customerGroup->setDiscount(0);
            $customerGroup->setMinimumOrder(0);
            $customerGroup->setMinimumOrderSurcharge(0);
            $this->model->persist($customerGroup);
            $this->model->flush();
        }

        return $customerGroup;
    }
}
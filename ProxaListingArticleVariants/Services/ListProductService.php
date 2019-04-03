<?php

namespace ProxaListingArticleVariants\Services;

use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ListProductServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct;

class ListProductService implements ListProductServiceInterface {

    /** @var ListProductServiceInterface  */
    private $service;

    /** @var ConfiguratorServiceInterface  */
    private $configuratorService;

    public function __construct(ListProductServiceInterface $service, ConfiguratorServiceInterface $configuratorService) {
        $this->service = $service;
        $this->configuratorService = $configuratorService;
    }

    public function get($number, Struct\ProductContextInterface $context) {
        return $this->service->get($number, $context);
    }

    public function getList(array $numbers, Struct\ProductContextInterface $context) {
        $listProducts = $this->service->getList($numbers, $context);
        $configuration = $this->configuratorService->getProductsConfigurations($listProducts, $context);
        
        $products = [];
        foreach ($listProducts as $listProduct) {
            $number = $listProduct->getNumber();
            $product = Struct\Product::createFromListProduct($listProduct);
            
            $products[$number] = $product;
            if (isset($configuration[$number])) {
                $product->setConfiguration($configuration[$number]);
            }
        }

        return $products;
    }

}

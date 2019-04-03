<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace ProductColorVariants\Services;

use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\ProductNumberService;
use Shopware\Bundle\StoreFrontBundle\Struct\ListProduct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ListProductService implements Service\ListProductServiceInterface
{
    /**
     * @var Gateway\ListProductGatewayInterface
     */
    private $productGateway;

    /**
     * @var Service\MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var Service\CheapestPriceServiceInterface
     */
    private $cheapestPriceService;

    /**
     * @var Service\GraduatedPricesServiceInterface
     */
    private $graduatedPricesService;

    /**
     * @var Service\PriceCalculationServiceInterface
     */
    private $priceCalculationService;

    /**
     * @var Service\MarketingServiceInterface
     */
    private $marketingService;

    /**
     * @var Service\VoteServiceInterface
     */
    private $voteService;

    /**
     * @var Service\CategoryServiceInterface
     */
    private $categoryService;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    /**
     * @var ContainerInterface $container
     */
    private $container;

    /**
     * @param ContainerInterface $container
     * @param Gateway\ListProductGatewayInterface      $productGateway
     * @param Service\GraduatedPricesServiceInterface  $graduatedPricesService
     * @param Service\CheapestPriceServiceInterface    $cheapestPriceService
     * @param Service\PriceCalculationServiceInterface $priceCalculationService
     * @param Service\MediaServiceInterface            $mediaService
     * @param Service\MarketingServiceInterface        $marketingService
     * @param Service\VoteServiceInterface             $voteService
     * @param Service\CategoryServiceInterface         $categoryService
     * @param \Shopware_Components_Config              $config
     */
    public function __construct(
        ContainerInterface $container,
        Gateway\ListProductGatewayInterface $productGateway,
        Service\GraduatedPricesServiceInterface $graduatedPricesService,
        Service\CheapestPriceServiceInterface $cheapestPriceService,
        Service\PriceCalculationServiceInterface $priceCalculationService,
        Service\MediaServiceInterface $mediaService,
        Service\MarketingServiceInterface $marketingService,
        Service\VoteServiceInterface $voteService,
        Service\CategoryServiceInterface $categoryService,
        \Shopware_Components_Config $config
    ) {
        $this->container = $container;
        $this->productGateway = $productGateway;
        $this->graduatedPricesService = $graduatedPricesService;
        $this->cheapestPriceService = $cheapestPriceService;
        $this->priceCalculationService = $priceCalculationService;
        $this->mediaService = $mediaService;
        $this->marketingService = $marketingService;
        $this->voteService = $voteService;
        $this->categoryService = $categoryService;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function get($number, Struct\ProductContextInterface $context)
    {
        $products = $this->getList([$number], $context);

        return array_shift($products);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $numbers, Struct\ProductContextInterface $context)
    {
        // faster replacement for array_unique()
        // see http://stackoverflow.com/questions/8321620/array-unique-vs-array-flip
        $numbers = array_keys(array_flip($numbers));

        $products = $this->productGateway->getList($numbers, $context);

        $covers = $this->mediaService->getCovers($products, $context);

        $graduatedPrices = $this->graduatedPricesService->getList($products, $context);

        $cheapestPrices = $this->cheapestPriceService->getList($products, $context);

        $voteAverages = $this->voteService->getAverages($products, $context);

        $categories = $this->categoryService->getProductsCategories($products, $context);

        /** @var ConfiguratorService $configuratorService */
        $configuratorService = $this->container->get('shopware_storefront.configurator_service');

        /** @var ProductNumberService $productNumberService */
        $productNumberService = $this->container->get("shopware_storefront.product_number_service");

        /** @var \Shopware_Components_Modules  $modules */
        $modules = $this->container->get('modules');

        $result = [];
        foreach ($numbers as $number) {
            if (!array_key_exists($number, $products)) {
                continue;
            }
            $product = $products[$number];

            $productConfigurator = $configuratorService->getProductConfigurator($product, $context, []);
            $groups = $productConfigurator->getGroups();
            foreach ($groups as $groupKey=>$group){
                $groupName = $group->getName();
                if($groupName == 'Color'){
                    $colorInfo = [];
                    $options = $group->getOptions();
                    foreach ($options as $optionKey=>$option){
                        $optionsAttributes = $option->getAttributes();
                        $core = $optionsAttributes['core'];
                        $colorHexCod = $core->get('variants_color');

                        $groupId = $group->getId();
                        $optionId = $option->getId();
                        $selection = [$groupId=>$optionId];

                        $categoryId = $this->container->get('front')->Request()->getParam('sCategory');

                        $productNumberService = $this->container->get("shopware_storefront.product_number_service");
                        $detailNumber = $productNumberService->getAvailableNumber($number, $context, $selection);
                        $article = $this->productGateway->get($detailNumber, $context);

                        $rewrite = $this->getLinksOfProduct($article, $categoryId, $context, true);

                        $colorInfo[] = $colorHexCod.'<>'.$rewrite['linkDetailsRewrited'];
                    }
                    $colorHexCodesImplode = implode(',',$colorInfo);
                    $product->setAdditional($colorHexCodesImplode);
                }
            }

            if (isset($covers[$number])) {
                $product->setCover($covers[$number]);
            }

            if (isset($graduatedPrices[$number])) {
                $product->setPriceRules($graduatedPrices[$number]);
            }

            if (isset($cheapestPrices[$number])) {
                $product->setCheapestPriceRule($cheapestPrices[$number]);
            }

            if (isset($voteAverages[$number])) {
                $product->setVoteAverage($voteAverages[$number]);
            }

            if (isset($categories[$number])) {
                $product->setCategories($categories[$number]);
            }

            $product->addAttribute('marketing', $this->marketingService->getProductAttribute($product));

            $this->priceCalculationService->calculateProduct($product, $context);

            if (!$this->isProductValid($product, $context)) {
                continue;
            }

            $product->setListingPrice($product->getCheapestUnitPrice());
            $product->setDisplayFromPrice((count($product->getPrices()) > 1 || $product->hasDifferentPrices()));
            $product->setAllowBuyInListing($this->allowBuyInListing($product));
            if ($this->config->get('calculateCheapestPriceWithMinPurchase')) {
                $product->setListingPrice($product->getCheapestPrice());
            }
            $result[$number] = $product;
        }

        return $result;
    }

    /**
     * Checks if the provided product is allowed to display in the store front for
     * the provided context.
     *
     * @param Struct\ListProduct             $product
     * @param Struct\ProductContextInterface $context
     *
     * @return bool
     */
    private function isProductValid(Struct\ListProduct $product, Struct\ProductContextInterface $context)
    {
        if (in_array($context->getCurrentCustomerGroup()->getId(), $product->getBlockedCustomerGroupIds())) {
            return false;
        }

        $prices = $product->getPrices();
        if (empty($prices)) {
            return false;
        }

        if ($this->config->get('hideNoInStock') && !$product->isAvailable() && !$product->hasAvailableVariant()) {
            return false;
        }

        $ids = array_map(function (Struct\Category $category) {
            return $category->getId();
        }, $product->getCategories());

        return in_array($context->getShop()->getCategory()->getId(), $ids);
    }

    /**
     * @param Struct\ListProduct $product
     *
     * @return bool
     */
    private function allowBuyInListing(Struct\ListProduct $product)
    {
        return !$product->hasConfigurator()
            && $product->isAvailable()
            && $product->getUnit()->getMinPurchase() <= 1
            && !$product->displayFromPrice();
    }

    /**
     * Creates different links for the product like `add to basket`, `add to note`, `view detail page`, ...
     *
     * @param ListProduct $product
     * @param int                                 $categoryId
     * @param bool                                $addNumber
     *
     * @return array
     */
    private function getLinksOfProduct(ListProduct $product, $categoryId, $context, $addNumber)
    {
        $baseFile = $this->config->get('baseFile');

        $detail = $baseFile . '?sViewport=detail&sArticle=' . $product->getId();
        if ($categoryId) {
            $detail .= '&sCategory=' . $categoryId;
        }

        $rewrite = Shopware()->Modules()->Core()->sRewriteLink($detail, $product->getName());

        if ($addNumber) {
            $rewrite .= strpos($rewrite, '?') !== false ? '&' : '?';
            $rewrite .= 'number=' . $product->getNumber();
        }

        $basket = $baseFile . '?sViewport=basket&sAdd=' . $product->getNumber();
        $note = $baseFile . '?sViewport=note&sAdd=' . $product->getNumber();
        $friend = $baseFile . '?sViewport=tellafriend&sDetails=' . $product->getId();
        $pdf = $baseFile . '?sViewport=detail&sDetails=' . $product->getId() . '&sLanguage=' . $context->getShop()->getId() . '&sPDF=1';

        return [
            'linkBasket' => $basket,
            'linkDetails' => $detail,
            'linkDetailsRewrited' => $rewrite,
            'linkNote' => $note,
            'linkTellAFriend' => $friend,
            'linkPDF' => $pdf,
        ];
    }
}

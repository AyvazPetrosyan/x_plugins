<?php

namespace ProxaListingArticleVariants\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ConfiguratorServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Shopware\Components\Theme\LessDefinition;

class Frontend implements SubscriberInterface {

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $viewDir;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var ConfiguratorServiceInterface
     */
    private $configuratorService;

    /**
     * @var LegacyStructConverter
     */
    private $legacyStructConverter;

    public function __construct(
        ContainerInterface $container,
        ContextServiceInterface $contextService,
        ConfiguratorServiceInterface $configuratorService,
        LegacyStructConverter $legacyStructConverter,
        $viewDir)
    {
        $this->container = $container;
        $this->contextService = $contextService;
        $this->configuratorService = $configuratorService;
        $this->legacyStructConverter = $legacyStructConverter;
        $this->viewDir = $viewDir;
    }

    public static function getSubscribedEvents() {
		return array(
            'Enlight_Controller_Action_PreDispatch_Widgets_Listing' => 'onWidgetsListingPreDispatch',
            'Enlight_Controller_Action_PostDispatch_Frontend_Listing' => 'onFrontendListingPostDispatch',
            'Legacy_Struct_Converter_List_Product_Data'  => 'listProductData',

            'Theme_Compiler_Collect_Plugin_Less' => 'addLessFiles'
		);
	}

	public function onWidgetsListingPreDispatch (\Enlight_Event_EventArgs $args){
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get("subject");
        $request = $controller->Request();
        $view = $controller->View();
        $actionName = $request->getActionName();
        $view->addTemplateDir($this->viewDir);
    }

	public function onFrontendListingPostDispatch (\Enlight_Event_EventArgs $args){
	    /** @var \Enlight_Controller_Action $controller */
	    $controller = $args->get("subject");
	    $view = $controller->View();
	    $view->addTemplateDir($this->viewDir);
    }

    public function listProductData (\Enlight_Event_EventArgs $args){
        $data = $args->getReturn();
        $product = $args->get("product");
        $configurator = $this->configuratorService->getProductConfigurator(
            $product,
            $this->contextService->getShopContext(),
            []
        );
        $convertedConfigurator = $this->legacyStructConverter->convertConfiguratorStruct($product, $configurator);

        foreach ($convertedConfigurator['sConfigurator'] as $kay => $articlesConfiguration){
            $groupID = $articlesConfiguration['groupID'];
            $groupName = $articlesConfiguration['groupname'];
            $opttionList = $articlesConfiguration['values'];

            $productList['optionsGroup'][$groupName]['groupID'] = $groupID;
            $productList['optionsGroup'][$groupName]['groupname'] = $groupName;
            foreach($opttionList as $option){
                $optionID = $option['optionID'];
                $productList['optionsGroup'][$groupName]['values'][$optionID]['optionName'] = $option['optionname'];
                $productList['optionsGroup'][$groupName]['values'][$optionID]['optionID'] = $option['optionID'];
            }
        }

        $data["sConfigurator"] = $productList;

        return $data;
    }

    public function addLessFiles(\Enlight_Event_EventArgs $args) {
//        $less = new LessDefinition(
//            [],
//            [ __DIR__ . '/../Resources/views/frontend/_public/src/less/all.less'],
//            __DIR__ . '/../Resources/views/frontend/_public/src/less'
//        );
//
//        return new ArrayCollection(array($less));
    }
}
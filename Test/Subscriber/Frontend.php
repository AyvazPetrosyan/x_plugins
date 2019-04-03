<?php

namespace Test\Subscriber;

use Shopware\Models\Attribute\Order;
use Shopware\Models\Attribute\Category;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Snippet\Snippet;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Test\Bundle\AttributeService\Attribute;
use Test\Bundle\phpBarCodeGeneratorMaster\Service\BarCodeGeneratorService;

/**
 * Class Frontend
 * @package Test\Subscriber
 */
class Frontend implements SubscriberInterface
{

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $pluginDir;

    /** @var string */
    private $viewDir;

    const SNIPPETS_NAMESPACE = "frontend/forms/elements";


    /**
     * Frontend constructor.
     * @param ContainerInterface $container
     * @param string $pluginDir
     * @param string $viewDir
     */
    public function __construct(
        ContainerInterface $container,
        $pluginDir,
        $viewDir
    )
    {
        $this->container = $container;

        $this->pluginDir = $pluginDir;
        $this->viewDir = $viewDir;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            "Enlight_Controller_Action_PostDispatch_Frontend_Forms" => "onPostDispatchFrontendForms",
            "Enlight_Controller_Action_PostDispatch_Frontend" => "onPostDispatchFrontend",
            "Shopware_Modules_Order_SaveOrder_ProcessDetails" => "saveOrder"
        ];
    }

    public function onPostDispatchFrontendForms(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $assignList = $view->getAssign();
        $sSupport = $assignList['sSupport'];
        $formName = $sSupport['name'];
        $sElements = $sSupport['sElements'];

        /** @var \Shopware_Components_Snippet_Manager $snippets */
        $snippets = $this->container->get('snippets');
        $snippetsNamespace = $snippets->getNamespace(self::SNIPPETS_NAMESPACE);

        foreach ($sElements as $sElementsKey => $sElement) {
            $fieldName = $sElement['name'];
            $snippetName = $formName . '_' . $fieldName;
            if ($sElement['note']) {
                $fieldNote = $snippetsNamespace->get($snippetName);
                if (!$this->checkSnippetValue(self::SNIPPETS_NAMESPACE, $snippetName, $sElement['note'])) {
                    $sSupport['sElements'][$sElementsKey]['note'] = $this->updateSnippet(self::SNIPPETS_NAMESPACE, $snippetName, $sElement['note']);
                } elseif ($fieldNote) {
                    $sSupport['sElements'][$sElementsKey]['note'] = $fieldNote;
                }
            }
        }

        $view->assign('sSupport', $sSupport);
    }

    private function checkSnippetValue($namespace, $name, $value)
    {
        $queryResultList = Shopware()->Db()->fetchAssoc(
            "SELECT * FROM `s_core_snippets` WHERE `namespace`=? AND `name`=?",
            [$namespace, $name]
        );

        foreach ($queryResultList as $resultKey => $result) {
            if ($result['value'] == $value) {
                return true;
            }
        }
        return false;
    }

    private function updateSnippet($namespace, $name, $value)
    {
        $queryResult = Shopware()->Db()->query(
            "DELETE FROM `s_core_snippets` WHERE `namespace`=? AND `name`=?",
            [$namespace, $name]
        );

        if ($queryResult) {
            $params = [
                'namespace' => $namespace,
                'value' => $value,
                'defaultValue' => '',
                'name' => $name,
                'shopId' => Shopware()->Shop()->getId(),
                'localeId' => Shopware()->Shop()->getLocale()->getId(),
            ];
            $snippet = new Snippet();
            $snippet->fromArray($params);
            $snippet->setDirty(true);

            Shopware()->Models()->persist($snippet);
            Shopware()->Models()->flush();

            return $value;
        }

        return false;
    }

    public function onPostDispatchFrontend(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->addTemplateDir($this->viewDir);

        /** @var Attribute $attrService */
        $attrService = $this->container->get('test.attribute_service');
        $attrService->setIntoTable(Category::class, ['id' => 3], 'Attribute1', '777');
        $result = $attrService->getAll(Category::class);
        $result1 = $attrService->getOneBy(Category::class, ['id' => 1]);
    }

    public function saveOrder(\sOrder $args)
    {
        /** @var \sOrder $sOrder */
        $sOrder = $args->get('subject');
        $orderId = $args->get('orderId');
        $orderNumber = $sOrder->sGetOrderNumber();

        /** @var BarCodeGeneratorService $barCodeService */
        $barCodeService = $this->container->get('test.bar_code_service');
        $barCode = $barCodeService->getBarCode($orderNumber);
        $this->setOrderBarcode($orderId, $barCode);
    }

    private function setOrderBarcode($orderId, $barCode)
    {
        /** @var ModelManager $models */
        $models = $this->container->get('models');
        $orderRepository = $models->getRepository(Order::class);
        $result = $orderRepository->findOneBy(['orderId' => $orderId]);
        $result->setOrderBarcode($barCode);
        $models->persist($result);
        $models->flush();
    }
}
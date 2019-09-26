<?php

namespace ZilProductImport\Subscriber;

use Enlight\Event\SubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Backend
 * @package ZilProductImport\Subscriber
 */
class Backend implements SubscriberInterface {

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $pluginDir;

    /** @var string */
    private $viewDir;


    /**
     * Backend constructor.
     * @param ContainerInterface $container
     * @param string $pluginDir
     * @param string $viewDir
     */
    public function __construct(
        ContainerInterface $container,
                           $pluginDir,
                           $viewDir
    ) {

        $this->container = $container;

        $this->pluginDir = $pluginDir;
        $this->viewDir   = $viewDir;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents() {
        return [
            "Enlight_Controller_Action_PostDispatch_Backend" => "onPostDispatchBackend"
        ];
    }

    public function onPostDispatchBackend(\Enlight_Event_EventArgs $args){
        /** @var \Shopware_Controllers_Backend_ProductImport $controller */
        $controller = $args->get("subject");

        $view = $controller->View();
        $view->addTemplateDir($this->viewDir);
    }
}
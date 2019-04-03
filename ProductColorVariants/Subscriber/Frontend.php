<?php

namespace ProductColorVariants\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Models\Shop\DetachedShop;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Frontend
 * @package ProductColorVariants\Subscriber
 */
class Frontend implements SubscriberInterface {

    /** @var ContainerInterface */
    private $container;

    /** @var string */
    private $pluginDir;

    /** @var string */
    private $viewDir;

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
            "Enlight_Controller_Action_PostDispatch_Frontend" => "onPostDispatchFrontend",
        ];
    }

    public function onPostDispatchFrontend(\Enlight_Controller_Action $args){
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->addTemplateDir($this->viewDir);
    }
}
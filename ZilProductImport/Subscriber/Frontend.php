<?php

namespace ZilProductImport\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Frontend
 * @package ZilProductImport\Subscriber
 */
class Frontend implements SubscriberInterface
{
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
            "Enlight_Controller_Action_PostDispatch_Frontend" => "onPostFrontend",
        ];
    }

    public function onPostFrontend(\Enlight_Event_EventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();
        $view->addTemplateDir($this->viewDir);
    }
}
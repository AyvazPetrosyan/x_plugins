<?php

namespace ZilProductImport\Services\Validation;


use Psr\Container\ContainerInterface;
use Shopware\Components\Model\ModelManager;
use ZilProductImport\Models\Package;
use ZilProductImport\Services\Files\FilesService;

class PackageValidation
{
    private $result = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->result = [
            'message' => '',
            'result' => true
        ];
        $this->container = $container;
    }

    public function packageValidation()
    {
        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");

        /** @var \ZilProductImport\Services\Package\Package $package */
        $package = $this->container->get('zil_product_import.services.Package');

        /** @var ModelManager $model */
        $model = $this->container->get('models');
        $importingPackage = $model->getRepository(Package::class)->findOneBy(['state' => $package::IN_PROCESS]);

        if(!empty($importingPackage)) {
            return [
                'status' => 'in_process',
                'message' => $snippetManager->get('PackageValidationError'),
                'result' => false
            ];
        }

        return $this->result;
    }

    public function packageNameValidation()
    {
        $pluginDir = $this->container->getParameter("zil_product_import.plugin_dir");
        /** @var \Enlight_Components_Snippet_Namespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");
        /** @var FilesService $fileService */
        $fileService = $this->container->get('zil_product_import.services.FilesService');
        $definedPackageFullName = $pluginDir . '/../../../' . $fileService::UPLOAD_DIR . '/' . $fileService::ZIP_NAME . '.' . $fileService::ZIP_FORMAT;
        if (!file_exists($definedPackageFullName)) {
            $this->result = [
                'message' => $snippetManager->get('PackageExistValidationError'),
                'result' => false
            ];
        }

        return $this->result;
    }
}
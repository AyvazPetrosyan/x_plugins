<?php

use Enlight_Components_Snippet_Namespace as SnippetNamespace;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use ZilProductImport\Services\Files\FilesService;
use ZilProductImport\Services\Package\Package;

/**
 * Class Upload
 */
class Shopware_Controllers_Backend_Upload extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @throws Exception
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $this->get("template")
            ->addTemplateDir($this->container->getParameter("zil_product_import.view_dir"));
    }

    public function indexAction()
    {
        parent::indexAction();
    }

    public function uploadAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '8000M');
        ini_set('upload_max_filesize', "10000M");
        $time_pre = microtime(true);
        $filesList = [];
        /** @var FilesService $filesService */
        $filesService = $this->container->get('zil_product_import.services.FilesService');

        /** @var SnippetNamespace $snippetsManager */
        $snippetsManager = $this->getSnippetManager();

        $file = $_FILES['file'];
        if (empty($file)) {
            $response = [
                "success" => false,
                "message" => json_encode(['success' => false, 'message' => $snippetsManager->get("UploadFileNotFoundErrorMessage")])
            ];
            $this->View()->assign($response);
            return false;
        }

        $fileTmpPath = $file['tmp_name'];
        $fileBag = new FileBag($_FILES);
        /** @var UploadedFile $file */
        $file = $fileBag->get('file');

        $this->Response()->setHeader('Content-Type', 'text/plain');

        $pluginDir = $this->container->getParameter("zil_product_import.plugin_dir");
        $filesDirName = $pluginDir . '/../../../' . $filesService::UPLOAD_DIR . '/' . $filesService::FILES_DIR_NAME;
        $uploadedFileFullName = $filesDirName . '/' . $file->getClientOriginalName();

        if (!is_dir($pluginDir . '/../../../' . $filesService::UPLOAD_DIR)) {
            mkdir($pluginDir . '/../../../' . $filesService::UPLOAD_DIR);
        }

        if (is_dir($filesDirName)) {
            $this->removeDir($filesDirName);
        } else {
            mkdir($filesDirName);
        }

        $uploadResponse = move_uploaded_file($fileTmpPath, $uploadedFileFullName);

        if ($uploadResponse) {
            $response = [
                "success" => true,
                "message" => $snippetsManager->get("ProductsSuccessfullyImported")
            ];

            $csvFile = $uploadedFileFullName; //$filesDirName . '/' . $filesService::FILES_NAME['productsName'] . '.' . $filesService::FILES_FORMAT;
            $filesList[] = $file->getClientOriginalName();

            /** @var \ZilProductImport\Services\ForceEncodingService $encoding */
            $encoding = $this->container->get("zil_product_import.services.ForceEncodingService");

            $filecontents = file_get_contents($csvFile);
            $filecontents = $encoding::toUTF8($filecontents);
            $filecontents = $encoding::removeBOM($filecontents);
            file_put_contents($csvFile, $filecontents);

            $packageName = pathinfo($csvFile, PATHINFO_FILENAME); //explode('.', $file->getClientOriginalName())[0]; //pathinfo($file) don`t worck with zip files

            /** @var Package $package */
            $package = $this->container->get('zil_product_import.services.Package');
            /** @var \ZilProductImport\Services\Upload\Upload $upload */
            $upload = $this->container->get('zil_product_import.services.Upload');
            $uploadResult = $upload->upload($packageName, $filesList);
            if (!$uploadResult['result']) {
                if ($uploadResult['status'] != 'in_process') {
                    $package->updatePackageInfo($packageName, $package::FAILED);
                }
                $this->View()->assign($uploadResult);
                return false;
            }

        } else {
            unlink($file->getPathname());
            $response = [
                "success" => false,
                "message" => $snippetsManager->get("UploadErrorMessage")
            ];
            $this->View()->assign($response);
            return false;
        }

        $this->View()->assign($response);

        /** @var \ZilProductImport\Services\Package\Package $package */
        $package = $this->container->get('zil_product_import.services.Package');
        if ($uploadResult['result']) {
            $packageState = $package::FINISHED;
        } else {
            $packageState = $package::FAILED;
        }

        $time_post = microtime(true);
        $exec_time = (string)($time_post - $time_pre);
        $exec_time = 'Import duration: ' . substr($exec_time, 0, 3) . ' seconds';

        $package->updatePackageInfo($packageName, $packageState, $exec_time);

        return true;
    }

    /**
     * @return SnippetNamespace
     */
    private function getSnippetManager()
    {
        /** @var SnippetNamespace $snippetManager */
        $snippetManager = $this->container->get("snippets")->getNamespace("zill_product_import/backend/main");

        return $snippetManager;
    }

    private function removeDir($path)
    {
        $files = glob($path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                if ($this->isDirEmpty($file)) {
                    rmdir($file);
                } else {
                    $this->removeDir($file);
                    rmdir($file);
                }
            }
        }

        return true;
    }

    private function isDirEmpty($dirName)
    {
        if (!is_dir($dirName)) return false;
        foreach (scandir($dirName) as $file) {
            if (!in_array($file, array('.', '..'))) return false;
        }
        return true;
    }

    private function convertCsvToUtf8($filename)
    {
        $filecontents = file_get_contents($filename);
        $filecontents = $this->toUTF8($filecontents);
        file_put_contents($filename, $filecontents);
    }

}
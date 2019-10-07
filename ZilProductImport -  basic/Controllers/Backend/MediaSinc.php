<?php

use ZilProductImport\Services\Files\FilesService;

/**
 * Class Shopware_Controllers_Frontend_ProductImport
 */
class Shopware_Controllers_Backend_MediaSinc extends Shopware_Controllers_Backend_ExtJs {

    public function indexAction()
    {
        set_time_limit(0);
        ini_set('memory_limit', '16000M');
        ini_set('upload_max_filesize', "10000M");
        $this->container->get('front')->Plugins()->ViewRenderer()->setNoRender();
        /** @var FilesService $fileService */
        $fileService = $this->container->get('zil_product_import.services.FilesService');
        /** @var \ZilProductImport\Services\Media\Media $customMediaService */
        $customMediaService = $this->container->get('zil_product_import.services.Media');
        /** @var \Shopware\Components\Model\ModelManager $model */
        $model = $this->container->get('models');

        $response = [
            "success" => true,
            "message" => 'Media sync finished successfully.'
        ];

        $mediaPackageModel = new \ZilProductImport\Models\MediaPackage();
        $mediaPackageModel->setName('media');
        $mediaPackageModel->setDate(date('y/m/d'));

        if (!$this->checkMediaPackagesStatus()) {
            $response = [
                "success" => false,
                "message" => json_encode(['success' => false, 'message' => 'You can not import two or more media packages at the same time.'])
            ];

            $mediaPackageModel->setMessage('You can not import two or more media packages at the same time.');
            $mediaPackageModel->setStatus($fileService::MEDIA_PACKAGE_STATUSES['filed']);
            $model->persist($mediaPackageModel);
            $model->flush();

            $this->view->assign(json_encode($response));

            return false;
        }

        try {
            $mediaPackageModel->setStatus($fileService::MEDIA_PACKAGE_STATUSES['inProcess']);
            $model->persist($mediaPackageModel);
            $model->flush();

            $imagesPath = $fileService->pluginDir . '/../../../' . $fileService::UPLOAD_DIR . '/' . $fileService::MEDIA_DIR_NAME;
            $imagesDir = scandir($imagesPath);

            Shopware()->Db()->query('DELETE FROM media_log');
            Shopware()->Db()->query('DELETE FROM s_articles_img');
            Shopware()->Db()->query('DELETE FROM s_media WHERE is_imported=1');
            /*Shopware()->Db()->query('UPDATE s_articles SET active=0');
            Shopware()->Db()->query('UPDATE s_articles_details SET active=0');*/

            $imageCount = 0;
            foreach ($imagesDir as $imageKey => $imageDirName) {
                if ($imageDirName == '.' || $imageDirName == '..') {
                    continue;
                }
                $productImages = $this->getImagesList($imageDirName);
                $imageCount += count($productImages);
                foreach ($productImages as $imageKey => $imageInfo) {
                    $mediaLog = new \ZilProductImport\Models\MediaLog();
                    $mediaLog->setMediaName($imageInfo['name']);
                    $mediaLog->setProductNumber($imageDirName);
                    $mediaLog->setAllMediaCount($imageCount);
                    $mediaLog->setResult($imageInfo['imageValidation']);
                    $mediaLog->setImportMessage($imageInfo['message']);
                    $model->persist($mediaLog);
                    $model->flush();

                    if ($imageInfo['imageValidation'] == 'failed') {
                        continue;
                    }
                    $customMediaService->importImages($imageInfo);
                }
            }
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());

            Shopware()->Db()->query('UPDATE `media_package` SET `status`=? WHERE `status`=?',[
                $fileService::MEDIA_PACKAGE_STATUSES['filed'],
                $fileService::MEDIA_PACKAGE_STATUSES['inProcess']
            ]);

            return false;
        }

        Shopware()->Db()->query('UPDATE `media_package` SET `status`=? WHERE `status`=?',[
            $fileService::MEDIA_PACKAGE_STATUSES['success'],
            $fileService::MEDIA_PACKAGE_STATUSES['inProcess']
        ]);

        $this->view->assign($response);
        return true;
    }

    public function mediaLocAction()
    {
        /** @var \Shopware\Components\Model\ModelManager $model */
        $model = $this->container->get('models');
        $mediaLocResult = $model->getRepository(\ZilProductImport\Models\MediaLog::class)->findAll();
        $resultList = $model->toArray($mediaLocResult);

        $this->view->assign('data', $resultList);
    }

    private function getImagesList($productNumber)
    {
        $pluginDir = $this->container->getParameter("zil_product_import.plugin_dir");
        /** @var FilesService $fileService */
        $fileService = $this->container->get('zil_product_import.services.FilesService');
        $productImagesDirPath = $pluginDir . '/../../../' . $fileService::UPLOAD_DIR . '/' . $fileService::MEDIA_DIR_NAME . '/' . $productNumber;

        $this->renameDirImages($productImagesDirPath, $productNumber);
        $packageProductImageNameList = scandir($productImagesDirPath);

        $step = 0;
        $packageProductImages = array();

        foreach ($packageProductImageNameList as $imageKey=>$packageProductImageName) {
            $imageValidation = 'success';
            $message = 'Success';
            if ($packageProductImageName == "." || $packageProductImageName == "..") {
                continue;
            }

            $imageExtetion = pathinfo($productImagesDirPath . "/" . $packageProductImageName, PATHINFO_EXTENSION);

            $extension = pathinfo($productImagesDirPath . "/" . $packageProductImageName, PATHINFO_EXTENSION);
            if (filesize($productImagesDirPath. "/" . $packageProductImageName) >= 10000000) {
                $imageValidation = 'failed';
                $message = 'Size more than 10 MB';
            } elseif (!in_array($imageExtetion, $fileService::IMAGE_EXTENSIONS)) {
                $imageValidation = 'failed';
                $message = 'The extension can`t be '.$extension;
            }
            $packageProductImages[$imageKey]['name'] = pathinfo($productImagesDirPath . "/" . $packageProductImageName, PATHINFO_FILENAME);
            $packageProductImages[$imageKey]['extension'] = pathinfo($productImagesDirPath . "/" . $packageProductImageName, PATHINFO_EXTENSION);
            $packageProductImages[$imageKey]['productNumber'] = $productNumber;
            $packageProductImages[$imageKey]['size'] = filesize($productImagesDirPath . "/" . $packageProductImageName);
            $packageProductImages[$imageKey]['imageValidation'] = $imageValidation;
            $packageProductImages[$imageKey]['message'] = $message;

            if ($imageValidation == 'success') {
                $step++;
            }
            if ($step == 1) {
                $packageProductImages[$imageKey]['isMain'] = 1;
            } else {
                $packageProductImages[$imageKey]['isMain'] = 2;
            }
        }

        return $packageProductImages;
    }

    private function renameDirImages($dir, $productNumber)
    {
        $packageProductImageNameList = scandir($dir);
        foreach ($packageProductImageNameList as $imageKey=>$imageName) {
            if ($imageName == "." || $imageName == "..") {
                continue;
            }

            $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
            $imageNewName = $productNumber . '-' . $imageKey . '-zil-media' . '.' . $imageExtension;
            rename($dir. '/' .$imageName, $dir. '/' .$imageNewName);
        }

        return true;
    }

    private function checkMediaPackagesStatus()
    {
        /** @var FilesService $fileService */
        $fileService = $this->container->get('zil_product_import.services.FilesService');

        $inProcessPackages = Shopware()->Db()->fetchAssoc('SELECT * FROM media_package WHERE `status` = ?',[
            $fileService::MEDIA_PACKAGE_STATUSES['inProcess']
        ]);

        if (empty($inProcessPackages)) {
            return true;
        }

        return false;
    }
}
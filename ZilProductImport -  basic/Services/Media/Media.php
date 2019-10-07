<?php

namespace ZilProductImport\Services\Media;

use Psr\Container\ContainerInterface;
use Shopware\Bundle\MediaBundle\MediaService;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Thumbnail\Manager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Media\Album;
use Shopware\Models\Media\Settings;
use ZilProductImport\Services\Files\FilesService;

class Media
{
    const MEDIA_DIR_PATH = 'public/media'; //'media/image/zil_imported_products'

    private $container;

    /** @var string */
    private $pluginDir;

    public function __construct(ContainerInterface $container, $pluginDir)
    {
        $this->container = $container;
        $this->pluginDir = $pluginDir;
    }

    public function getMediaDirPath()
    {
        return $this->pluginDir . '/../../../files/' . self::MEDIA_DIR_PATH;
    }

    public function getMediaDirUrl()
    {
        return 'http://' . Shopware()->Shop()->getHost() . '/files/' . self::MEDIA_DIR_PATH;
    }

    public function generateImageInfo($productNumber, $onlyMain = false)
    {
        $imageInfo = false;
        $imagePath = $this->getMainImageUrl($productNumber);
        if ($onlyMain) {
            $imageInfo = [
                'source' => $imagePath,
                'thumbnails' => [
                    0 => [
                        'source' => $imagePath,
                        'retinaSource' => $imagePath,
                        'sourceSet' => $imagePath
                    ],
                    1 => [
                        'source' => $imagePath,
                        'retinaSource' => $imagePath,
                        'sourceSet' => $imagePath
                    ],
                    2 => [
                        'source' => $imagePath,
                        'retinaSource' => $imagePath,
                        'sourceSet' => $imagePath
                    ],
                ]
            ];
        } else {
            $imagesUrl = $this->getImagesUrl($productNumber);
            array_shift($imagesUrl);
            foreach ($imagesUrl as $imageKey => $image) {
                $imageInfo[] = [
                    'source' => $image,
                    'thumbnails' => [
                        0 => [
                            'source' => $image,
                            'retinaSource' => $image,
                            'sourceSet' => $image
                        ],
                        1 => [
                            'source' => $image,
                            'retinaSource' => $image,
                            'sourceSet' => $image
                        ],
                        2 => [
                            'source' => $image,
                            'retinaSource' => $image,
                            'sourceSet' => $image
                        ],
                    ]
                ];
            }
        }

        return $imageInfo;
    }

    public function importImages($imageInfo, $thumbnailSizes)
    {
        try {
            /** @var FilesService $fileService */
            $fileService = $this->container->get('zil_product_import.services.FilesService');

            if (!$thumbnailSizes) {
                $thumbnailSizes = $fileService::THUMBNAIL_SIZES;
            }

            $name = $imageInfo['name'];
            $extension = $imageInfo['extension'];
            $size = $imageInfo['size'];
            if (!$size) {
                $size = '3000';
            }
            $virtualPath = 'media/image/' . $name . '.' . $extension;
            $main = $imageInfo['isMain'];
            $productNumber = $imageInfo['productNumber'];
            $type = 'IMAGE';

            /** @var ModelManager $model */
            $model = $this->container->get('models');
            /** @var Detail $productDetail */
            $productDetail = $model->getRepository(Detail::class)->findOneBy(['number' => $productNumber]);
            if (empty($productDetail)) {
                return false;
            }
            $kind = $productDetail->getKind();

            /** @var Article $product */
            $product = $productDetail->getArticle();
            if (empty($product)) {
                return false;
            }

            if ($productDetail->getInStock() > 0) {
                $productDetail->setActive(1);
                $model->persist($productDetail);
                $model->flush();

                $product->setActive(1);
                $model->persist($product);
                $model->flush();
            }

            /** @var Album $mediaAlbum */
            $mediaAlbum = $model->getRepository(Album::class)->findOneBy(['name' => 'Artikel']);
            $albumSettings = $mediaAlbum->getSettings();
            if (empty($mediaAlbum)) {
                $mediaAlbum = new Album();
                $mediaAlbum->setName('Artikel');
                $mediaAlbum->setPosition(2);
                $mediaAlbum->setGarbageCollectable(1);
                $mediaAlbum->setSettings();
                $model->persist($mediaAlbum);
                $model->flush();
            }

            if (!$albumSettings) {
                $settings = new Settings();
                $settings->setThumbnailHighDpi(1);
                $settings->setCreateThumbnails(1);
                $settings->setThumbnailSize($fileService::ALBUM_SETTING_THUMBNAIL_SIZES);
                $settings->setThumbnailQuality(90);
                $settings->setThumbnailHighDpiQuality(60);
                $settings->setIcon('sprite-inbox');
                $settings->setAlbum($mediaAlbum);
                $model->persist($settings);
                $model->flush();
            }

            $media = new \Shopware\Models\Media\Media();
            $media->setName($name);
            $media->setPath($virtualPath);
            $media->setAlbumId($mediaAlbum->getId());
            $media->setAlbum($mediaAlbum);
            $media->setDefaultThumbnails();
            $media->setExtension($extension);
            $media->setUserId(50);
            $media->setDescription('');
            $media->setType($type);
            $media->setFileSize($size);
            $media->setCreated(date("Y/m/d"));
            $model->persist($media);
            $model->flush();

            Shopware()->Db()->query('UPDATE `s_media` SET is_imported=1 WHERE id = ?', [$media->getId()]);

            $mainImage = $model->getRepository(Image::class)->findOneBy(['articleId' => $product->getId()]);
            if ($mainImage) {
                $main = 2;
            }

            $image = new Image();
            $image->setExtension($extension);
            $image->setWidth(0);
            $image->setHeight(0);
            $image->setPosition(1);
            $image->setDescription('');
            $image->setMedia($media);
            $image->setArticle($product);
            $image->setArticleDetail(null); // for main image
            $image->setParent(null); // for main image
            $image->setPath($name); //image name
            $image->setMain($main);
            $model->persist($image);
            $model->flush();

            if ($kind == 2) {
                $mediaId = $media->getId();
                $parentImage = $model->getRepository(Image::class)->findOneBy(['media' => $mediaId]);

                $image = new Image();
                $image->setExtension($extension);
                $image->setWidth(0);
                $image->setHeight(0);
                $image->setPosition(1);
                $image->setDescription('');
                $image->setMedia(null); // for variant image
                $image->setArticle(null); // for variant image
                $image->setArticleDetail($productDetail);
                $image->setParent($parentImage);
                $image->setPath($name); //image name
                $image->setMain(2);
                $model->persist($image);
                $model->flush();
            }

            $model->clear();

            $imageFullName = $this->getMediaDirPath() . '/' . $productNumber . '/' . $name . '.' . $extension;
            $fileContent = fopen(
                $imageFullName,
                'r'
            );

            /** @var MediaService $mediaService */
            $mediaService = $this->container->get('shopware_media.media_service');
            /** @var Manager $thumbnailManager */
            $thumbnailManager = $this->container->get('thumbnail_manager');

            $mediaService->write($virtualPath, $fileContent);
            $thumbnailManager->createMediaThumbnail($media, $thumbnailSizes, true);

            $result = true;

            return $result;
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->error('ZilProductImport ' . $e->getMessage());
        }
    }

    private function getMainImageUrl($productNumber)
    {
        if (empty($productNumber)) {
            return false;
        }

        $productImagesDirPath = $this->getMediaDirPath() . '/' . $productNumber;
        $images = scandir($productImagesDirPath);
        $mainImage = $images[2];
        if (!$mainImage) {
            return null;
        }
        $mainImageUrl = $this->getMediaDirUrl() . '/' . $productNumber . '/' . $mainImage;

        return $mainImageUrl;
    }

    private function getImagesUrl($productNumber)
    {
        if (empty($productNumber)) {
            return false;
        }

        $productImagesDirPath = $this->getMediaDirPath() . '/' . $productNumber;
        $images = scandir($productImagesDirPath);
        $imagesUrl = [];
        $step = 0;
        foreach ($images as $imageKey => $image) {
            if (!$image) {
                $imagesUrl[] = false;
                $step++;
                continue;
            }
            if ($step > 1) {
                $imagesUrl[] = $this->getMediaDirUrl() . '/' . $productNumber . '/' . $image;
            }
            $step++;
        }

        return $imagesUrl;
    }
}
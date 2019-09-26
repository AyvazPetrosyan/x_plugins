<?php

namespace ZilProductImport\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_log")
 */
class MediaLog extends ModelEntity
{
    /**
     * Primary Key - autoincrement value
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $mediaName
     * @ORM\Column(name="media_name", type="string", nullable=true)
     */
    private $mediaName;

    /**
     * @var string $productNumber
     * @ORM\Column(name="product_number", type="string", nullable=true)
     */
    private $productNumber;

    /**
     * @var string $result
     * @ORM\Column(name="result", type="string", nullable=true)
     */
    private $result;

    /**
     * @var integer $allMediaCount
     * @ORM\Column(name="all_media_count", type="integer", nullable=true)
     */
    private $allMediaCount;

    /**
     * @var string $importMessage
     * @ORM\Column(name="import_message", type="string", nullable=true)
     */
    private $importMessage;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $mediaName
     */
    public function setMediaName($mediaName)
    {
        $this->mediaName = $mediaName;
    }

    /**
     * @return string
     */
    public function getMediaName()
    {
        return $this->mediaName;
    }

    /**
     * @param string $productNumber
     */
    public function setProductNumber($productNumber)
    {
        $this->productNumber = $productNumber;
    }

    /**
     * @return string
     */
    public function getProductNumber()
    {
        return $this->productNumber;
    }

    /**
     * @param string $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return string
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param int $allMediaCount
     */
    public function setAllMediaCount($allMediaCount)
    {
        $this->allMediaCount = $allMediaCount;
    }

    /**
     * @return int
     */
    public function getAllMediaCount()
    {
        return $this->allMediaCount;
    }

    /**
     * @param string $importMessage
     */
    public function setImportMessage($importMessage)
    {
        $this->importMessage = $importMessage;
    }

    /**
     * @return string
     */
    public function getImportMessage()
    {
        return $this->importMessage;
    }
}
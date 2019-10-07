<?php

namespace ZilProductImport\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="package_detail")
 */
class PackageDetail extends ModelEntity
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
     * @var int $packageId
     * @ORM\Column(name="package_id", type="integer", nullable=false)
     */
    private $packageId;

    /**
     * @var string $productNumber
     * @ORM\Column(name="product_number", type="string", nullable=true)
     */
    private $productNumber;

    /**
     * @var string $productImportDescription
     * @ORM\Column(name="product_import_description", type="string", nullable=true)
     */
    private $productImportDescription;

    /**
     * @var string $importState
     * @ORM\Column(name="import_state", type="string", nullable=true)
     */
    private $importState;

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
     * @param int $packageId
     */
    public function setPackageId($packageId)
    {
        $this->packageId = $packageId;
    }

    /**
     * @return int
     */
    public function getPackageId()
    {
        return $this->packageId;
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
     * @param string $productImportDescription
     */
    public function setProductImportDescription($productImportDescription)
    {
        $this->productImportDescription = $productImportDescription;
    }

    /**
     * @return string
     */
    public function getProductImportDescription()
    {
        return $this->productImportDescription;
    }

    /**
     * @param string $importState
     */
    public function setImportState($importState)
    {
        $this->importState = $importState;
    }

    /**
     * @return string
     */
    public function getImportState()
    {
        return $this->importState;
    }
}
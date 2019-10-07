<?php

namespace ZilProductImport\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="package")
 */
class Package extends ModelEntity
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
     * @var string $packageName
     * @ORM\Column(name="package_name", type="string", nullable=true)
     */
    private $packageName;

    /**
     * @var string $importDate
     * @ORM\Column(name="import_date", type="string", nullable=true)
     */
    private $importDate;

    /**
     * @var integer $productCount
     * @ORM\Column(name="product_count", type="integer", nullable=true)
     */
    private $productCount;

    /**
     * @var integer $importedProductCount
     * @ORM\Column(name="imported_product_count", type="integer", nullable=true)
     */
    private $importedProductCount;

    /**
     * @var integer $updatedProductCount
     * @ORM\Column(name="updated_product_count", type="integer", nullable=true)
     */
    private $updatedProductCount;

    /**
     * @var string $state
     * @ORM\Column(name="package_state", type="string", nullable=true)
     */
    private $state;

    /**
     * @var string $packageImportDescription
     * @ORM\Column(name="package_import_description", type="string", nullable=false)
     */
    private $packageImportDescription;

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
     * @param string $packageName
     */
    public function setPackageName($packageName)
    {
        $this->packageName = $packageName;
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @param date $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * @return date
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * @param int $productCount
     */
    public function setProductCount($productCount)
    {
        $this->productCount = $productCount;
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @param int $importedProductCount
     */
    public function setImportedProductCount($importedProductCount)
    {
        $this->importedProductCount = $importedProductCount;
    }

    /**
     * @return int
     */
    public function getImportedProductCount()
    {
        return $this->importedProductCount;
    }

    /**
     * @param int $updatedProductCount
     */
    public function setUpdatedProductCount($updatedProductCount)
    {
        $this->updatedProductCount = $updatedProductCount;
    }

    /**
     * @return int
     */
    public function getUpdatedProductCount()
    {
        return $this->updatedProductCount;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $packageImportDescription
     */
    public function setPackageImportDescription($packageImportDescription)
    {
        $this->packageImportDescription = $packageImportDescription;
    }

    /**
     * @return string
     */
    public function getPackageImportDescription()
    {
        return $this->packageImportDescription;
    }
}
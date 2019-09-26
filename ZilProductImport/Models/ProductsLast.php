<?php

namespace ZilProductImport\Models;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="products_last")
 */
class ProductsLast extends ModelEntity
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
     * @var string $productNumber
     * @ORM\Column(name="product_number", type="string", nullable=false)
     */
    private $productNumber;

    /**
     * @var string $parentNumber
     * @ORM\Column(name="parent_number", type="string", nullable=true)
     */
    private $parentNumber;

    /**
     * @var string $name
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $descriptionEn
     * @ORM\Column(name="description_en", type="string", nullable=true)
     */
    private $descriptionEn;

    /**
     * @var string $descriptionRu
     * @ORM\Column(name="description_ru", type="string", nullable=true)
     */
    private $descriptionRu;

    /**
     * @var string $descriptionAm
     * @ORM\Column(name="description_am", type="string", nullable=true)
     */
    private $descriptionAm;
    /**
     * @var string $composure
     * @ORM\Column(name="composure", type="string", nullable=true)
     */
    private $composure;

    /**
     * @var string $images
     * @ORM\Column(name="images", type="string", nullable=true)
     */
    private $images;

    /**
     * @var string $size
     * @ORM\Column(name="size", type="string", nullable=true)
     */
    private $size;

    /**
     * @var string $color
     * @ORM\Column(name="color", type="string", nullable=true)
     */
    private $color;

    /**
     * @var string $manufacturer
     * @ORM\Column(name="manufacturer", type="string", nullable=true)
     */
    private $manufacturer;

    /**
     * @var string $firstPrice
     * @ORM\Column(name="first_price", type="string", nullable=false)
     */
    private $firstPrice;

    /**
     * @var string $lastPrice
     * @ORM\Column(name="last_price", type="string", nullable=true)
     */
    private $lastPrice;

    /**
     * @var bool $isMain
     * @ORM\Column(name="is_main", type="boolean", nullable=true)
     */
    private $isMain;

    /**
     * @var integer $stock
     * @ORM\Column(name="stock", type="integer", nullable=false)
     */
    private $stock;

    /**
     * @var string $categories
     * @ORM\Column(name="categories", type="string", nullable=true)
     */
    private $categories;

    /**
     * @var string $productStatus
     * @ORM\Column(name="product_status", type="string", nullable=true)
     */
    private $productStatus;

    /**
     * @var string $hash
     * @ORM\Column(name="hash", type="string", nullable=true)
     */
    private $hash;

    /**
     * @var bool $active
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var bool $isOnlineShop
     * @ORM\Column(name="is_online_shop", type="boolean", nullable=false)
     */
    private $isOnlineShop;

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
     * @param string $parentNumber
     */
    public function setParentNumber($parentNumber)
    {
        $this->parentNumber = $parentNumber;
    }

    /**
     * @return string
     */
    public function getParentNumber()
    {
        return $this->parentNumber;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $descriptionEn
     */
    public function setDescriptionEn($descriptionEn)
    {
        $this->descriptionEn = $descriptionEn;
    }

    /**
     * @return string
     */
    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    /**
     * @param string $descriptionRu
     */
    public function setDescriptionRu($descriptionRu)
    {
        $this->descriptionRu = $descriptionRu;
    }

    /**
     * @return string
     */
    public function getDescriptionRu()
    {
        return $this->descriptionRu;
    }

    /**
     * @param string $descriptionAm
     */
    public function setDescriptionAm($descriptionAm)
    {
        $this->descriptionAm = $descriptionAm;
    }

    /**
     * @return string
     */
    public function getDescriptionAm()
    {
        return $this->descriptionAm;
    }

    /**
     * @param string $composure
     */
    public function setComposure($composure)
    {
        $this->composure = $composure;
    }

    /**
     * @return string
     */
    public function getComposure()
    {
        return $this->composure;
    }

    /**
     * @param string $images
     */
    public function setImages($images)
    {
        $this->images = $images;
    }

    /**
     * @return string
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @param string $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $manufacturer
     */
    public function setManufacturer($manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    /**
     * @return string
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    /**
     * @param string $firstPrice
     */
    public function setFirstPrice($firstPrice)
    {
        $this->firstPrice = $firstPrice;
    }

    /**
     * @return string
     */
    public function getFirstPrice()
    {
        return $this->firstPrice;
    }

    /**
     * @param string $lastPrice
     */
    public function setLastPrice($lastPrice)
    {
        $this->lastPrice = $lastPrice;
    }

    /**
     * @return string
     */
    public function getLastPrice()
    {
        return $this->lastPrice;
    }

    /**
     * @param bool $isMain
     */
    public function setIsMain($isMain)
    {
        $this->isMain = $isMain;
    }

    /**
     * @return bool
     */
    public function getIsMain()
    {
        return $this->isMain;
    }

    /**
     * @param int $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @return string
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param string $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return string
     */
    public function getProductStatus()
    {
        return $this->productStatus;
    }

    /**
     * @param string $productStatus
     */
    public function setProductStatus($productStatus)
    {
        $this->productStatus = $productStatus;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getIsOnlineShop()
    {
        return $this->isOnlineShop;
    }

    /**
     * @param bool $isOnlineShop
     */
    public function setIsOnlineShop($isOnlineShop)
    {
        $this->isOnlineShop = $isOnlineShop;
    }
}
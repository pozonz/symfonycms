<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Ramsey\Uuid\Uuid;

trait ProductTrait
{
    protected $_gallery;

    protected $_variants;

    protected $_brand = null;

    protected $_category = null;

    protected $_categories = [];

    /**
     * ProductVariantTrait constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->productUniqid = Uuid::uuid4()->toString();
    }

    /**
     * @return mixed|null
     */
    public function getSiteSearchImage()
    {
        return $this->thumbnail;
    }

    /**
     * @param $variant
     */
    public function addVariant($variant)
    {
        if (gettype($this->_variants) != 'array') {
            $this->_variants = [];
        }
        $this->_variants[] = $variant;
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public function objVariant()
    {
        if (!$this->_variants) {
            $this->_variants = $this->objVariants();
        }
        return count($this->_variants) ? array_shift($this->_variants) : null;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function objVariants()
    {
        if (!$this->_variants) {
            $fullClass = UtilsService::getFullClassFromName('ProductVariant');
            $this->_variants = $fullClass::active($this->_connection, [
                'whereSql' => 'm.productUniqid = ?',
                'params' => [$this->productUniqid],
            ]);
        }
        return $this->_variants;
    }

    /**
     * @return array|false|string
     */
    public function objImage()
    {
        $gallery = $this->objGallery();
        if (count($gallery) > 0) {
            return $gallery[0]->id;
        } else {
            return $_ENV['PRODUCT_PLACEHOLDER_ID'];
        }
    }

    /**
     * @return string
     */
    public function objProductPageUrl()
    {
        return $this->getSiteMapUrl();
    }

    /**
     * @return mixed
     */
    public function objGallery()
    {
        if (!$this->_gallery) {
            $fullClass = UtilsService::getFullClassFromName('Asset');
            $jsonGallery = json_decode($this->gallery ?: '[]');
            $this->_gallery = array_filter(array_map(function ($itm) use ($fullClass) {
                return $fullClass::getById($this->_connection, $itm);
            }, $jsonGallery));
        }
        return $this->_gallery;
    }

    /**
     * @return bool
     */
    public function objOnSaleActive()
    {
        if (!$this->onSale) {
            return false;
        }
        if ($this->saleStart && strtotime($this->saleStart) > time()) {
            return false;
        }
        if ($this->saleEnd && strtotime($this->saleEnd) < time()) {
            return false;
        }
        return true;
    }

    /**
     * @param bool $doNotSaveVersion
     * @param array $options
     * @return mixed|null
     * @throws \Exception
     */
    public function save($options = [])
    {
        $this->_saveProductCachedData();
        $result = parent::save($options);

        /** @var Model $model */
        $model = static::getModel($this->_connection);
        $tableName = $model->getTableName();
        $sql = "UPDATE `$tableName` SET `_slug` = ? WHERE `id` = ?";
        $stmt = $this->_connection->prepare($sql);
        $stmt->execute([$this->_slug . '-' . $this->id, $this->id]);

        return $result;
    }

    /**
     * @param array $options
     */
    public function _saveProductCachedData($options = [])
    {
        $this->thumbnail = $this->objImage();

        $variantCount = 0;
        $variantDisabledCount = 0;
        $lowStock = 0;
        $outOfStock = 0;

        $this->price = null;
        $fullClass = UtilsService::getFullClassFromName('ProductVariant');
        $data = $fullClass::data($this->_connection, [
            'whereSql' => 'm.productUniqid = ?',
            'params' => [$this->productUniqid],
        ]);
        foreach ($data as $itm) {
            $variantCount++;

            if (!$itm->_status) {
                $variantDisabledCount++;
            }

            if (!$itm->objOutOfStock() && $itm->objLowStock() == 1) {
                $lowStock++;
            }

            if ($itm->objOutOfStock() == 1) {
                $outOfStock++;
            }

//            if (!isset($options['doNotUpdatePrice']) || $options['doNotUpdatePrice'] != 1) {
            if ($this->price == null || $this->price > $itm->price) {
                $this->price = $itm->price;
                $this->salePrice = $itm->salePrice;
            }
//            }
        }

        $this->variantCount = $variantCount;
        $this->variantDisabledCount = $variantDisabledCount;
        $this->lowStock = $lowStock > 0 ? (count($data) == $lowStock ? 1 : 2) : 0;
        $this->outOfStock = $outOfStock > 0 ? (count($data) == $outOfStock ? 1 : 2) : 0;
    }

    /**
     * @return array|null
     */
    public function objCategory()
    {
        if (!$this->_category) {
            $categories = $this->objCategories();
            $this->_category = array_shift($categories);
        }
        return $this->_category;
    }

    /**
     * @return array|null
     */
    public function objCategories()
    {
        if (!$this->_categories) {
            $fullClass = UtilsService::getFullClassFromName('ProductCategory');
            $this->_categories = array_filter(array_map(function ($itm) use ($fullClass) {
                return $fullClass::getActiveById($this->_connection, $itm);
            }, json_decode($this->categories ?: '[]')));
        }
        return $this->_categories;
    }

    /**
     * @param $brand
     */
    public function setObjBrand($brand)
    {
        $this->_brand = $brand;
    }

    /**
     * @return array|null
     */
    public function objBrand()
    {
        if (!$this->_brand) {
            $fullClass = UtilsService::getFullClassFromName('ProductBrand');
            $this->_brand = $fullClass::getActiveById($this->_connection, $this->_brand);
        }
        return $this->_brand;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function objPriceFromAndTo()
    {
        $variants = $this->objVariants();

        $prices = array_map(function ($variant) {
            return $this->objOnSaleActive() && $variant->salePrice ? $variant->salePrice : $variant->price;
        }, $variants);

        return [
            'priceFrom' => min($prices),
            'priceTo' => max($prices),
        ];
    }

    /**
     * @param $num
     * @return array
     */
    public function objRelatedProducts($num = 3)
    {
        $fullClass = UtilsService::getFullClassFromName('Product');

        $objRelatedProducts = explode(',', $this->relatedProducts);
        $objRelatedProducts = array_filter(array_map(function ($itm) use ($fullClass) {
            return $fullClass::getById($this->_connection, $itm);
        }, $objRelatedProducts));

        if (!count($objRelatedProducts)) {
            $objRelatedProducts = $fullClass::active($this->_connection, [
                'whereSql' => 'm.id != ?',
                'params' => [$this->id],
                'limit' => $num,
                'sort' => 'rand()',
            ]);
        }
        return $objRelatedProducts;
    }

}
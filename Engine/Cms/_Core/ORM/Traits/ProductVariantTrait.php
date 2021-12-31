<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait ProductVariantTrait
{
    protected $_product;

    /**
     * ProductVariantTrait constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->shippingUnits = 1;
    }

    /**
     * @param $product
     */
    public function setObjProduct($product)
    {
        $this->_product = $product;
    }

    /**
     * @return mixed
     */
    public function objProduct()
    {
        if (!$this->_product) {
            $fullClass = UtilsService::getFullClassFromName('Product');
            $this->_product = $fullClass::getByField($this->_connection, 'productUniqid', $this->productUniqid);
        }
        return $this->_product;
    }

    /**
     * @param $customer
     * @return float|int
     */
    public function calculatedSalePrice($customer)
    {
        $price = $this->salePrice ?: 0;
        return $this->_getCalculatedPrice($this, $customer, $price);
    }

    /**
     * @param $customer
     * @return float|int
     */
    public function calculatedPrice($customer)
    {
        $price = $this->price ?: 0;
        return $this->_getCalculatedPrice($this, $customer, $price);
    }

    /**
     * @param $productOrVariant
     * @param $customer
     * @param $price
     * @return float|int
     */
    public function _getCalculatedPrice($productOrVariant, $customer, $price)
    {
        $product = $this->objProduct();
        if (!$product->noMemberDiscount || !$customer) {
            return $price;
        }

//        $customerMembership = $customer->objMembership();
//        if (!$customerMembership || !$customerMembership->getDiscount()) {
//            return $price;
//        }
//        return $price * ((100 - $customerMembership->getDiscount()) / 100);
    }

    /**
     * @return int
     */
    public function objLowStock()
    {
        if (!$this->stockEnabled) {
            return 0;
        }
        if ($this->alertIfLessThan > 0 && $this->alertIfLessThan > $this->stock) {
            return 1;
        }
        return 0;
    }

    /**
     * @return int
     */
    public function objOutOfStock()
    {
        if (!$this->stockEnabled) {
            return 0;
        }
        if ($this->stock > 0) {
            return 0;
        }
        return 1;
    }

    /**
     * @param array $options
     * @return string|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function save($options = [])
    {
        $result = parent::save($options);

        $orm = $this->objProduct();
        if ($orm) {
            $orm->save();
        }

        return $result;
    }
}
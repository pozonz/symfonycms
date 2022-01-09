<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait OrderTrait
{
    protected $_orderItems;

    public function __construct(Connection $connection)
    {
        $this->category = 0;
        $this->createAnAccount = 0;
        $this->billingSame = 1;
        $this->billingSave = 0;
        $this->billingUseExisting = 0;
        $this->shippingSave = 0;
        $this->shippingUseExisting = 0;

        parent::__construct($connection);
    }

    /**
     * @return mixed|string
     */
    public function objShippingAddress()
    {
        $address = $this->shippingAddress;
        if ($this->shippingApartmentNo) {
            $address = $this->shippingApartmentNo . ', ' . $address;
        }

        $address = $this->shippingFirstName . ' ' . $this->shippingLastName . ', ' . $address;


        if ($this->shippingAddress2) {
            $address = $address . ', ' . $this->shippingAddress2;
        }
        if ($this->shippingCity) {
            $address = $address . ', ' . $this->shippingCity;
        }
        if ($this->shippingPostcode) {
            $address = $address . ' ' . $this->shippingPostcode;
        }
        if ($this->shippingCountry) {
            $address = $address . ', ' . $this->shippingCountry;
        }
        return $address;
    }

    /**
     * @return mixed|string
     */
    public function objBillingAddress()
    {
        if ($this->billingSame) {
            return $this->objShippingAddress();
        }
        $address = $this->billingAddress;
        if ($this->billingApartmentNo) {
            $address = $this->billingApartmentNo . ', ' . $address;
        }

        $address = $this->billingFirstName . ' ' . $this->billingLastName . ', ' . $address;

        if ($this->billingAddress2) {
            $address = $address . ', ' . $this->billingAddress2;
        }
        if ($this->billingCity) {
            $address = $address . ', ' . $this->billingCity;
        }
        if ($this->billingPostcode) {
            $address = $address . ' ' . $this->billingPostcode;
        }
        if ($this->billingCountry) {
            $address = $address . ', ' . $this->billingCountry;
        }
        return $address;
    }

    /**
     * @param $orderItems
     */
    public function setOrderItems($orderItems)
    {
        $this->_orderItems = $orderItems;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function objOrderItems()
    {
        if (!$this->_orderItems) {
            $fullClass = UtilsService::getFullClassFromName('OrderItem');
            $this->_orderItems = $fullClass::active($this->_connection, [
                'whereSql' => 'm.orderId = ?',
                'params' => array($this->id),
                'sort' => 'm.id',
                'order' => 'DESC',
            ]);
        }
        return $this->_orderItems;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function _jsonOrderItems()
    {
        return $this->objOrderItems();
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function objCustomer()
    {
        $fullClass = UtilsService::getFullClassFromName('Customer');
        return $fullClass::active($this->_connection, [
            'whereSql' => 'm.id = ?',
            'params' => array($this->customerId),
            'limit' => 1,
            'oneOrNull' => 1,
        ]);
    }

    /**
     * @return mixed
     */
    public function objHummRequestQuery()
    {
        return (array)json_decode($this->hummRequestQuery);
    }

    /**
     * @return string
     */
    public function _displayTotal()
    {
        return '$' . number_format($this->total, 2);
    }

    /**
     * @param array $options
     */
    protected function _beforeSave($options = [])
    {
        parent::_beforeSave($options);

        $this->firstName = $this->isPickup == 1 ? $this->pickupFirstName : $this->shippingFirstName;
        $this->lastName = $this->isPickup == 1 ? $this->pickupLastName : $this->shippingLastName;
    }
}
<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait ShippingByWeightTrait
{
    /**
     * @return mixed
     */
    public function objShippingCostRates()
    {
        return json_decode($this->shippingCostRates ?: '[]');
    }

    /**
     * @return array|null
     */
    public function objCountry()
    {
        $fullClass = UtilsService::getFullClassFromName('ShippingZone');
        return $fullClass::getById($this->_connection, $this->country);
    }
}
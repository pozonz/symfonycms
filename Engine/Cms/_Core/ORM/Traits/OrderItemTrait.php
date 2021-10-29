<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait OrderItemTrait
{
    protected $_variant;

    /**
     * @return mixed
     * @throws \Exception
     */
    public function objVariant()
    {
        if (!$this->_variant) {
            $fullClass = UtilsService::getFullClassFromName('ProductVariant');
            $this->_variant = $fullClass::getById($this->_connection, $this->productId);
        }
        return $this->_variant;
    }
}
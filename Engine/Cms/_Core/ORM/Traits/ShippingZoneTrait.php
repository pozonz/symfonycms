<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Traits;

trait ShippingZoneTrait
{
    /**
     * @return mixed
     */
    public function objChildren()
    {
        return static::active($this->_connection, [
            'whereSql' => 'm.parentId = ?',
            'params' => [$this->id],
        ]);
    }
}
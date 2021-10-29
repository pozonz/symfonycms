<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

trait PromoCodeTrait
{
    /**
     * @return bool
     */
    public function isValid() {
        if ($this->_status != 1) {
            return false;
        }
        if ($this->start && strtotime($this->start) >= time()) {
            return false;
        }
        if ($this->end && strtotime($this->end) <= time()) {
            return false;
        }
        return true;
    }
}
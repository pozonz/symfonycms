<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait FormSubmissionTrait
{
    /**
     * @return mixed
     */
    public function objFormBuilder()
    {
        $fullClass = UtilsService::getFullClassFromName('FormBuilder');
        return $fullClass::getById($this->_connection, $this->formDescriptorId);
    }
}
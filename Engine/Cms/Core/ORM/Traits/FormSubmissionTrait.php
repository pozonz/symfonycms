<?php

namespace ExWife\Engine\Cms\Core\ORM\Traits;

use ExWife\Engine\Cms\Core\Service\UtilsService;

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
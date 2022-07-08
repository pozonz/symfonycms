<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Traits;

use SymfonyCMS\Engine\Cms\Page\Service\PageService;

trait PageTemplateTrait
{
    /**
     * @param array $options
     * @return string|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function save($options = [])
    {
        PageService::createTemplateFile($this);
        return parent::save($options);
    }
}
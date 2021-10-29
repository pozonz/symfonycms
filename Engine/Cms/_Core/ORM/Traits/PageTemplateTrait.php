<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use ExWife\Engine\Cms\Page\Service\PageService;

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
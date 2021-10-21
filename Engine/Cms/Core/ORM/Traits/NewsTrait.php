<?php

namespace ExWife\Engine\Cms\Core\ORM\Traits;

use ExWife\Engine\Cms\Core\Service\UtilsService;

trait NewsTrait
{
    /**
     * @return array
     */
    public function objRelatedBlog()
    {
        $relatedBlog = json_decode($this->relatedBlog ?: '[]');
        return array_values(array_filter(array_map(function ($itm) {
            return static::getById($this->_connection, $itm);
        }, $relatedBlog)));
    }

    /**
     * @return array
     */
    public function objCategories()
    {
        $fullClass = UtilsService::getFullClassFromName('NewsCategory');
        $relatedBlog = json_decode($this->categories ?: '[]');
        return array_values(array_filter(array_map(function ($itm) use ($fullClass) {
            return $fullClass::getById($this->_connection, $itm);
        }, $relatedBlog)));
    }
}
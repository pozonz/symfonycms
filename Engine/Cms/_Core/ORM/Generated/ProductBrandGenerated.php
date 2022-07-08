<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Generated;

use SymfonyCMS\Engine\Cms\_Core\Base\ORM\BaseORM;
use SymfonyCMS\Engine\Cms\_Core\Version\VersionInterface;
use SymfonyCMS\Engine\Cms\_Core\Version\VersionTrait;
use SymfonyCMS\Engine\Cms\_Core\SiteSearch\SiteSearchInterface;
use SymfonyCMS\Engine\Cms\_Core\SiteSearch\SiteSearchTrait;
use SymfonyCMS\Engine\Cms\_Core\ManageSearch\ManageSearchInterface;
use SymfonyCMS\Engine\Cms\_Core\ManageSearch\ManageSearchTrait;

class ProductBrandGenerated extends BaseORM implements ManageSearchInterface
{
    use ManageSearchTrait;

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
}
<?php

namespace ExWife\Engine\Cms\Core\ORM\Generated;

use ExWife\Engine\Cms\Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\Core\Version\VersionInterface;
use ExWife\Engine\Cms\Core\Version\VersionTrait;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchInterface;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchTrait;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchInterface;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchTrait;

class ImageSizeGenerated extends BaseORM implements ManageSearchInterface
{
    use ManageSearchTrait;

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $dataType;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $code;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $resizeBy;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $width;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $showInCrop;
   
}
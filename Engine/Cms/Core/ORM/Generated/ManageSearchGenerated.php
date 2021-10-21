<?php

namespace ExWife\Engine\Cms\Core\ORM\Generated;

use ExWife\Engine\Cms\Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\Core\Version\VersionInterface;
use ExWife\Engine\Cms\Core\Version\VersionTrait;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchInterface;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchTrait;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchInterface;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchTrait;

class ManageSearchGenerated extends BaseORM 
{
    

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $category;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $description;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $image;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $url;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $ormId;
   
    /**
     * #pz mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $searchKeywords;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $modelnitials;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $modelTitle;
   
}
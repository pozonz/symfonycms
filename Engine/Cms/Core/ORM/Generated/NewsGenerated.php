<?php

namespace ExWife\Engine\Cms\Core\ORM\Generated;

use ExWife\Engine\Cms\Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\Core\Version\VersionInterface;
use ExWife\Engine\Cms\Core\Version\VersionTrait;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchInterface;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchTrait;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchInterface;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchTrait;

class NewsGenerated extends BaseORM implements VersionInterface, ManageSearchInterface, SiteSearchInterface
{
    use VersionTrait, ManageSearchTrait, SiteSearchTrait;

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz datetime DEFAULT NULL
     */
    public $date;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $image;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $heroCaption;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $categories;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $excerpts;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $featured;
   
    /**
     * #pz mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $contentBlocks;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $relatedBlog;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $hideFromSearch;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $searchKeywords;
   
}
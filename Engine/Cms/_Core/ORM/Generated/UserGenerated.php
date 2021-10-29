<?php

namespace ExWife\Engine\Cms\_Core\ORM\Generated;

use ExWife\Engine\Cms\_Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\_Core\Version\VersionInterface;
use ExWife\Engine\Cms\_Core\Version\VersionTrait;
use ExWife\Engine\Cms\_Core\SiteSearch\SiteSearchInterface;
use ExWife\Engine\Cms\_Core\SiteSearch\SiteSearchTrait;
use ExWife\Engine\Cms\_Core\ManageSearch\ManageSearchInterface;
use ExWife\Engine\Cms\_Core\ManageSearch\ManageSearchTrait;

class UserGenerated extends BaseORM implements ManageSearchInterface
{
    use ManageSearchTrait;

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $passwordInput;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $password;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $name;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $email;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $accessibleSections;
   
}
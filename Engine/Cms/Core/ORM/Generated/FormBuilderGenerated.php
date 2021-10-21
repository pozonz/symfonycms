<?php

namespace ExWife\Engine\Cms\Core\ORM\Generated;

use ExWife\Engine\Cms\Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\Core\Version\VersionInterface;
use ExWife\Engine\Cms\Core\Version\VersionTrait;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchInterface;
use ExWife\Engine\Cms\Core\SiteSearch\SiteSearchTrait;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchInterface;
use ExWife\Engine\Cms\Core\ManageSearch\ManageSearchTrait;

class FormBuilderGenerated extends BaseORM implements ManageSearchInterface
{
    use ManageSearchTrait;

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $code;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $formName;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $fromAddress;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $recipients;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $antispam;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $formFields;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $formOverviewText;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $thankyouHeading;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $sendThankYouEmail;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $thankYouEmailSubject;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $thankYouEmailText;
   
}
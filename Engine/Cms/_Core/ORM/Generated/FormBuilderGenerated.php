<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Generated;

use SymfonyCMS\Engine\Cms\_Core\Base\ORM\BaseORM;
use SymfonyCMS\Engine\Cms\_Core\Version\VersionInterface;
use SymfonyCMS\Engine\Cms\_Core\Version\VersionTrait;
use SymfonyCMS\Engine\Cms\_Core\SiteSearch\SiteSearchInterface;
use SymfonyCMS\Engine\Cms\_Core\SiteSearch\SiteSearchTrait;
use SymfonyCMS\Engine\Cms\_Core\ManageSearch\ManageSearchInterface;
use SymfonyCMS\Engine\Cms\_Core\ManageSearch\ManageSearchTrait;

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
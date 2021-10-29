<?php

namespace ExWife\Engine\Cms\_Core\ORM\Generated;

use ExWife\Engine\Cms\_Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\_Core\Version\VersionInterface;
use ExWife\Engine\Cms\_Core\Version\VersionTrait;
use ExWife\Engine\Cms\_Core\SiteSearch\SiteSearchInterface;
use ExWife\Engine\Cms\_Core\SiteSearch\SiteSearchTrait;
use ExWife\Engine\Cms\_Core\ManageSearch\ManageSearchInterface;
use ExWife\Engine\Cms\_Core\ManageSearch\ManageSearchTrait;

class FormSubmissionGenerated extends BaseORM 
{
    

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $uniqueId;
   
    /**
     * #pz datetime DEFAULT NULL
     */
    public $date;
   
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
    public $content;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $contentWithField;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $emailStatus;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $emailRequest;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $emailResponse;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $formDescriptorId;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $formName;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $url;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $ip;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $country;
   
}
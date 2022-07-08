<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class CmsMenuItemModel extends Model
{
    public $id = 'b0b83acd-6d0b-44bb-89cd-7b15e7b7f8a5';
   
    public $_uniqid = '8a64fec4-1084-4ad0-a8cb-2fb6dd292963';
   
    public $_slug = 'cms-menu-items';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-02 10:32:44';
   
    public $_modified = '2021-10-20 18:31:48';
   
    public $_publishFrom = null;
   
    public $_publishTo = null;
   
    public $_userId = null;
   
    public $_versionUuid = null;
   
    public $_versionOrmId = null;
   
    public $_isDraft = null;
   
    public $_draftName = null;
   
    public $_isBootstrapData = null;
   
    public $_isArchived = null;
   
    public $_displayAdded = '0';
   
    public $_displayModified = '0';
   
    public $_displayUser = '0';
   
    public $title = 'CMS menu items';
   
    public $className = 'CmsMenuItem';
   
    public $modelCategory = '2';
   
    public $listingType = '1';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '["3"]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Icon:","field":"icon","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"200","queryable":1,"chosen":false,"selected":false},{"id":"extra2","label":"Load from config","field":"loadFromConfig","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"100","listingTitle":"Customisation","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Config:","field":"config","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
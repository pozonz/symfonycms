<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class ManageSearchModel extends Model
{
    public $id = '5e777c63-9f48-4e17-b0bd-58addedcf6a2';
   
    public $_uniqid = 'dd9318f7-b943-4c2a-b8ba-33fcf69fd41d';
   
    public $_slug = 'manage-searches';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-20 12:01:58';
   
    public $_modified = '2021-10-20 19:08:23';
   
    public $_publishFrom = null;
   
    public $_publishTo = null;
   
    public $_userId = '1';
   
    public $_versionUuid = '';
   
    public $_versionOrmId = null;
   
    public $_isDraft = null;
   
    public $_draftName = '';
   
    public $_isBootstrapData = null;
   
    public $_isArchived = null;
   
    public $_displayAdded = '0';
   
    public $_displayModified = '0';
   
    public $_displayUser = '0';
   
    public $title = 'Manage searches';
   
    public $className = 'ManageSearch';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = '_rank';
   
    public $defaultOrderBy = 'ASC';
   
    public $accesses = '["3"]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"category","label":"Category:","field":"category","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"200","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"shortdescription","label":"Description:","field":"description","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"image","label":"Image:","field":"image","widget":"Asset picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"url","label":"Url:","field":"url","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra1","label":"ORM ID:","field":"ormId","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"content","label":"Searchable keywords:","field":"searchKeywords","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Model initials:","field":"modelnitials","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false},{"id":"extra3","label":"model title:","field":"modelTitle","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
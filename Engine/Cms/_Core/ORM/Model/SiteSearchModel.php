<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class SiteSearchModel extends Model
{
    public $id = 'd9cf555d-8bc9-471a-9037-5a9602270614';
   
    public $_uniqid = '918e2f2f-6bd9-4875-9dcc-180fe62fb012';
   
    public $_slug = 'site-searches';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-15 17:20:50';
   
    public $_modified = '2021-10-15 18:03:35';
   
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
   
    public $title = 'Site searches';
   
    public $className = 'SiteSearch';
   
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
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"category","label":"Category:","field":"category","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"200","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"shortdescription","label":"Description:","field":"description","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"image","label":"Image:","field":"image","widget":"Asset picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"url","label":"Url:","field":"url","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra1","label":"ORM ID:","field":"ormId","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"content","label":"Searchable keywords:","field":"searchKeywords","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
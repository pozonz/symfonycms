<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class RedirectModel extends Model
{
    public $id = '41fea02c-1e50-4b2c-858b-14b40ca72da0';
   
    public $_uniqid = 'b96337ea-ccd3-46f4-94d9-d9bc808f00e3';
   
    public $_slug = 'redirects';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-15 16:11:50';
   
    public $_modified = '2021-10-20 18:38:57';
   
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
   
    public $title = 'Redirects';
   
    public $className = 'Redirect';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'title';
   
    public $defaultOrderBy = 'ASC';
   
    public $accesses = '["18"]';
   
    public $frontendUrl = '{{title}}';
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"URL:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"listingTitle":"URL","displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"shortdescription","label":"Redirect to:","field":"to","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"500","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"category","label":"Type:","field":"type","widget":"Choice","required":0,"unique":0,"sqlQuery":"SELECT 301 AS `key`, \'Permanent\' AS value \nUNION SELECT 302 AS `key`, \'Temporary \' AS value \nUNION SELECT 307 AS `key`, \'307\' AS value","listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"date5","label":"Last happened:","field":"lasthappened","widget":"Date picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"value","label":"Count:","field":"count","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"author","label":"Referers:","field":"referers","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
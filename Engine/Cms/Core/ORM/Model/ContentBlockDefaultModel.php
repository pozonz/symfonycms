<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class ContentBlockDefaultModel extends Model
{
    public $id = '663a47e6-b85e-4436-96a0-dcf6cd00f07d';
   
    public $_uniqid = 'd7205cc0-86e0-494c-b6a0-2b571b78c5f1';
   
    public $_slug = 'content-block-default';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-03 12:21:12';
   
    public $_modified = '2021-10-20 18:32:10';
   
    public $_publishFrom = null;
   
    public $_publishTo = null;
   
    public $_userId = '1';
   
    public $_versionUuid = '';
   
    public $_versionOrmId = null;
   
    public $_isDraft = null;
   
    public $_draftName = null;
   
    public $_isBootstrapData = null;
   
    public $_isArchived = null;
   
    public $_displayAdded = '0';
   
    public $_displayModified = '0';
   
    public $_displayUser = '0';
   
    public $title = 'Content block default';
   
    public $className = 'ContentBlockDefault';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'title';
   
    public $defaultOrderBy = 'ASC';
   
    public $accesses = '[]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Choice","required":1,"unique":1,"sqlQuery":"FROM _Model","listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"fax","label":"Data type:","field":"dataType","widget":"Choice","required":1,"unique":0,"sqlQuery":"SELECT 1 AS `key`, \'Customised\' AS value UNION SELECT 2 AS `key`, \'Built in\' AS value","listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"shortdescription","label":"Field:","field":"attr","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"200","queryable":0,"chosen":false,"selected":false},{"id":"content","label":"Content:","field":"content","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
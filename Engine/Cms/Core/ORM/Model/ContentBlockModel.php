<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class ContentBlockModel extends Model
{
    public $id = '26f3940a-64a5-4b27-97f6-decfc5528960';
   
    public $_uniqid = '00a920dd-f8e3-4dce-a549-ce4de295953a';
   
    public $_slug = 'content-blocks';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-03 12:20:02';
   
    public $_modified = '2021-10-20 18:32:32';
   
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
   
    public $title = 'Content blocks';
   
    public $className = 'ContentBlock';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'title';
   
    public $defaultOrderBy = 'ASC';
   
    public $accesses = '[]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"fax","label":"Data type:","field":"dataType","widget":"Choice","required":1,"unique":0,"sqlQuery":"SELECT 1 AS `key`, \'Customised\' AS value UNION SELECT 2 AS `key`, \'Built in\' AS value","listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra1","label":"File name:","field":"twig","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"400","queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Tags:","field":"tags","widget":"Choice multi","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM ContentBlockTag WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":1,"listingWidth":"400","queryable":0,"chosen":false,"selected":false,"displayFunc":"displayTags"},{"id":"extra3","label":"Items:","field":"items","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class ShippingZoneModel extends Model
{
    public $id = '09ca3bad-2ee1-4441-aab7-c926072baf49';
   
    public $_uniqid = 'b182f104-062f-4ef6-9d77-54c519536dad';
   
    public $_slug = 'shipping-zones';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-08 15:51:26';
   
    public $_modified = '2021-10-15 14:51:09';
   
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
   
    public $title = 'Shipping zones';
   
    public $className = 'ShippingZone';
   
    public $modelCategory = '2';
   
    public $listingType = '3';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Code:","field":"code","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Parent:","field":"parentId","widget":"Choice tree","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value, parentId AS parentId FROM ShippingZone WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
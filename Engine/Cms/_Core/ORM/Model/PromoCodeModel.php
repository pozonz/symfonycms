<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class PromoCodeModel extends Model
{
    public $id = '5b2c8c04-b16f-47ec-b90c-3d6064b195a6';
   
    public $_uniqid = '43857493-9b17-40f7-a582-6049240bb1ba';
   
    public $_slug = 'promo-codes';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-08 15:24:44';
   
    public $_modified = '2021-10-20 18:38:39';
   
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
   
    public $title = 'Promo codes';
   
    public $className = 'PromoCode';
   
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
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Code:","field":"code","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Discount type:","field":"type","widget":"Choice","required":0,"unique":0,"sqlQuery":"SELECT 1 AS `key`, \'$\' AS value \nUNION SELECT 2 AS `key`, \'%\' AS value \n","listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Discount value:","field":"value","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"startdate","label":"Start:","field":"start","widget":"Date picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"enddate","label":"End:","field":"end","widget":"Date picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
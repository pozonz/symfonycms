<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class ShippingByWeightModel extends Model
{
    public $id = 'e99db855-981b-4165-b09f-48ee4fd5a4bb';
   
    public $_uniqid = 'a02e459e-2d0e-4810-9c64-bb507c77cbed';
   
    public $_slug = 'shipping-methods';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-08 15:28:49';
   
    public $_modified = '2021-10-15 14:50:51';
   
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
   
    public $title = 'Shipping methods';
   
    public $className = 'ShippingByWeight';
   
    public $modelCategory = '2';
   
    public $listingType = '1';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Shipping country:","field":"country","widget":"Choice","required":0,"unique":0,"sqlQuery":"SELECT \n    id AS `key`, title AS value\nFROM\n    ShippingZone\nWHERE\n    (parentId IS NULL OR parentId = \'\') AND `_versionOrmId` IS NULL\nORDER BY FIELD(value, \'Canada\', \'United Kingdom (UK)\', \'United States (US)\', \'Australia\', \'New Zealand\') DESC, title ASC","listing":1,"listingWidth":"400","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Free shipping if order price is above than ($):","field":"freeDeliveryIfPriceAbove","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Shipping method description (if required):","field":"shippingMethod","widget":"Wysiwyg","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"Shipping cost rates:","field":"shippingCostRates","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
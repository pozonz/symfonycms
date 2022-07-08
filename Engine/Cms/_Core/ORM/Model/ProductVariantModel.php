<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class ProductVariantModel extends Model
{
    public $id = 'c4342471-5187-4ba2-9b39-c88fdae7c343';
   
    public $_uniqid = '698fc2f9-1d28-4071-a635-7a15e389dc10';
   
    public $_slug = 'product-variants';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-08 15:03:45';
   
    public $_modified = '2021-10-13 15:40:20';
   
    public $_publishFrom = null;
   
    public $_publishTo = null;
   
    public $_userId = '1';
   
    public $_versionUuid = '';
   
    public $_versionOrmId = null;
   
    public $_isDraft = null;
   
    public $_draftName = '';
   
    public $_isBootstrapData = null;
   
    public $_isArchived = null;
   
    public $_displayAdded = null;
   
    public $_displayModified = null;
   
    public $_displayUser = null;
   
    public $title = 'Product variants';
   
    public $className = 'ProductVariant';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"category","label":"Product:","field":"productUniqid","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra1","label":"SKU:","field":"sku","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Retail price:","field":"price","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Sale price:","field":"salePrice","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"Stock tracking enabled?","field":"stockEnabled","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"Stock:","field":"stock","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"Alert if less than:","field":"alertIfLessThan","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra7","label":"Shipping unit(s):","field":"shippingUnits","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
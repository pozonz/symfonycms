<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class ProductModel extends Model
{
    public $id = 'ed6aaf97-a51a-45f2-8fe5-c4f2eff31dd7';
   
    public $_uniqid = 'b308845d-ef5c-47aa-ae35-ac1ee4ba3d16';
   
    public $_slug = 'products';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-08 14:44:12';
   
    public $_modified = '2021-10-20 18:38:25';
   
    public $_publishFrom = null;
   
    public $_publishTo = null;
   
    public $_userId = '1';
   
    public $_versionUuid = '';
   
    public $_versionOrmId = null;
   
    public $_isDraft = null;
   
    public $_draftName = '';
   
    public $_isBootstrapData = null;
   
    public $_isArchived = null;
   
    public $_displayAdded = '1';
   
    public $_displayModified = '0';
   
    public $_displayUser = '0';
   
    public $title = 'Products';
   
    public $className = 'Product';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'pageRank';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = '/product/{{_slug}}';
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '1';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Code:","field":"sku","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"100","displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"category","label":"Categories:","field":"categories","widget":"Choice tree multi","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value, parentId AS parentId FROM ProductCategory WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":1,"listingWidth":"300","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Brand:","field":"brand","widget":"Choice","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM ProductBrand WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":1,"listingWidth":"200","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Ranking number (bigger = higher rank on page):","field":"pageRank","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"75","displayFunc":null,"queryable":0,"chosen":false,"selected":false,"listingTitle":"Rank"},{"id":"extra4","label":"Related products:","field":"relatedProducts","widget":"Choice sortable","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM Product WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"No membership discount?","field":"noMemberDiscount","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"No promo code discount?","field":"noPromoDiscount","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra7","label":"Gallery:","field":"gallery","widget":"Asset files picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra8","label":"Description:","field":"description","widget":"Wysiwyg","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"content","label":"Product variants:","field":"productVariants","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"startdate","label":"Sale start:","field":"saleStart","widget":"Date picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"enddate","label":"Sale end:","field":"saleEnd","widget":"Date picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra9","label":"On sale?","field":"onSale","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra14","label":"Hide from search:","field":"hideFromSearch","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra15","label":"Searchable keywords (Not visible to public):","field":"searchKeywords","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra16","label":"Price:","field":"price","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra10","label":"Sale price:","field":"salePrice","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra11","label":"Out of stock?","field":"outOfStock","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra12","label":"Low stock?","field":"lowStock","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra13","label":"Thumbnail:","field":"thumbnail","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"fax","label":"Product uniqid:","field":"productUniqid","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra17","label":"Variant count:","field":"variantCount","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"75","listingTitle":"Var","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra18","label":"Variant disabled count:","field":"variantDisabledCount","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":"50","listingTitle":" ","displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
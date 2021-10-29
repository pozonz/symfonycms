<?php

namespace ExWife\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Model\Model;

class ImageCropModel extends Model
{
    public $id = '0a3e7221-6337-41f8-a7a2-001b02eb06b9';
   
    public $_uniqid = '2cd13154-e5a5-46d5-8c44-2e0c2ec61b34';
   
    public $_slug = 'image-cropping-sizes';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-06 11:06:38';
   
    public $_modified = '2021-10-13 15:40:16';
   
    public $_publishFrom = null;
   
    public $_publishTo = null;
   
    public $_userId = '1';
   
    public $_versionUuid = '';
   
    public $_versionOrmId = null;
   
    public $_isDraft = null;
   
    public $_draftName = null;
   
    public $_isBootstrapData = null;
   
    public $_isArchived = null;
   
    public $_displayAdded = null;
   
    public $_displayModified = null;
   
    public $_displayUser = null;
   
    public $title = 'Image cropping sizes';
   
    public $className = 'ImageCrop';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = null;
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1},{"id":"extra1","label":"X:","field":"x","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false},{"id":"extra2","label":"Y:","field":"y","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false},{"id":"extra3","label":"Width:","field":"width","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false},{"id":"extra4","label":"Height:","field":"height","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false},{"id":"extra5","label":"Asset ID:","field":"assetId","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false},{"id":"extra6","label":"Asset Size ID:","field":"assetSizeId","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
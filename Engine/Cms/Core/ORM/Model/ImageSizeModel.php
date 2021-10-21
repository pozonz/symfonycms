<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class ImageSizeModel extends Model
{
    public $id = '2d7f0c70-bbb2-45f5-b8f1-e9235e556ec6';
   
    public $_uniqid = '95552da6-a40d-4b54-8ffb-d1b8865bc484';
   
    public $_slug = 'image-sizes';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-03 12:29:59';
   
    public $_modified = '2021-10-20 18:35:01';
   
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
   
    public $title = 'Image sizes';
   
    public $className = 'ImageSize';
   
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
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"fax","label":"Data type:","field":"dataType","widget":"Choice","required":1,"unique":0,"sqlQuery":"SELECT 1 AS `key`, \'Customised\' AS value UNION SELECT 2 AS `key`, \'Built in\' AS value","listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Code:","field":"code","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":"200","queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Resize by:","field":"resizeBy","widget":"Choice","required":1,"unique":0,"sqlQuery":"SELECT 0 AS `key`, \'Width\' AS value \nUNION SELECT 1 AS `key`, \'Height\'","listing":1,"listingWidth":"200","queryable":0,"chosen":false,"selected":false},{"id":"latitude","label":"Width or height:","field":"width","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"300","queryable":0,"chosen":false,"selected":false,"listingTitle":"Size"},{"id":"extra1","label":"Show in cropping:","field":"showInCrop","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
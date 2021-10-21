<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class AssetBinaryModel extends Model
{
    public $id = '292a9686-c1d7-4138-84a9-46c69ea20bb8';
   
    public $_uniqid = '1ab226a0-5a18-4f8b-84c3-128897c06455';
   
    public $_slug = 'asset-binary';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-06 11:07:58';
   
    public $_modified = '2021-10-13 15:40:14';
   
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
   
    public $title = 'Asset binary';
   
    public $className = 'AssetBinary';
   
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
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1},{"id":"blob","label":"Blob:","field":"blob","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
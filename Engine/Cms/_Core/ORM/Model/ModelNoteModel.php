<?php

namespace ExWife\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Model\Model;

class ModelNoteModel extends Model
{
    public $id = '2c5a4efa-6167-453e-80f8-90bc85346175';
   
    public $_uniqid = 'e3e19202-c27e-4641-8310-02c2d6f07aad';
   
    public $_slug = 'model-notes';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-18 11:48:50';
   
    public $_modified = '2021-10-18 16:21:09';
   
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
   
    public $title = 'Model notes';
   
    public $className = 'ModelNote';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'title';
   
    public $defaultOrderBy = 'ASC';
   
    public $accesses = '["3"]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Choice","required":1,"unique":1,"sqlQuery":"From _Model","listing":1,"listingWidth":"200","listingTitle":"Model","displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Note:","field":"note","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
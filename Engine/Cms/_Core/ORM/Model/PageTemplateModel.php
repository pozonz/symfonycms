<?php

namespace ExWife\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Model\Model;

class PageTemplateModel extends Model
{
    public $id = '35e706ff-fc31-467b-a2ef-fd01f661cbdd';
   
    public $_uniqid = 'c1c56330-0eb2-417b-a8cd-4ee7282d11c9';
   
    public $_slug = 'page-templates';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-03 11:32:15';
   
    public $_modified = '2021-10-20 18:35:44';
   
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
   
    public $title = 'Page templates';
   
    public $className = 'PageTemplate';
   
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
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"File name:","field":"fileName","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"400","displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
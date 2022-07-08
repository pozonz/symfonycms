<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class AssetModel extends Model
{
    public $id = '4d1979db-55f8-44aa-9760-dc7d82f30aa8';
   
    public $_uniqid = 'f839bd7e-bccc-46f4-8d95-4b6eec9971b9';
   
    public $_slug = 'assets';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-06 11:04:25';
   
    public $_modified = '2021-10-20 18:31:38';
   
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
   
    public $title = 'Assets';
   
    public $className = 'Asset';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = '/images/assets/{{code}}';
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '1';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Code:","field":"code","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Alt:","field":"alt","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Description:","field":"description","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"url","label":"URL (if hyperlinked):","field":"url","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"File name:","field":"fileName","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"File type:","field":"fileType","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"File size:","field":"fileSize","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra7","label":"File location:","field":"fileLocation","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra8","label":"File extension:","field":"fileExtension","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra9","label":"Is folder:","field":"isFolder","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra10","label":"Parent:","field":"parentId","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra11","label":"Is image:","field":"isImage","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra12","label":"Width:","field":"width","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra13","label":"Height:","field":"height","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra14","label":"Hide from search:","field":"hideFromSearch","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra15","label":"Searchable keywords (Not visible to public):","field":"searchKeywords","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
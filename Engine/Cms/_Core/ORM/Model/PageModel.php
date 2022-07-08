<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class PageModel extends Model
{
    public $id = '1c49a022-2f10-4f39-994a-53fe43e4774f';
   
    public $_uniqid = '42fe9374-2154-4fca-80c7-b69791cad850';
   
    public $_slug = 'pages';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-03 10:41:28';
   
    public $_modified = '2021-10-20 18:36:02';
   
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
   
    public $title = 'Pages';
   
    public $className = 'Page';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = '{{url}}';
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '1';
   
    public $enableVersioning = '1';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Type:","field":"type","widget":"Choice","required":1,"unique":0,"sqlQuery":"SELECT 1 AS `key`, \'General\' AS `value`\nUNION SELECT 2 AS `key`, \'Redirect\' AS `value`","listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"url","label":"URL fragment:","field":"url","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra14","label":"Redirect to:","field":"redirectTo","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"category","label":"Category:","field":"category","widget":"Choice multi","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM PageCategory WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Template file:","field":"templateFile","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"ICON class (optional):","field":"iconClass","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Page title:","field":"pageTitle","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"Page heading:","field":"pageHeading","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"image","label":"Image:","field":"image","widget":"Asset picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"Attached models:","field":"attachedModels","widget":"Choice multi","required":0,"unique":0,"sqlQuery":"FROM _Model","listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"content","label":"Content:","field":"content","widget":"Content blocks","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra7","label":"Hide from frontend nav?:","field":"hideFromFrontendNav","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra8","label":"Hide from CMS nav?:","field":"hideFromCmsNav","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra9","label":"Allow extra URL slugs?:","field":"allowExtra","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra10","label":"Max extra URL slugs:","field":"maxParams","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra11","label":"Category rank:","field":"categoryRank","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra12","label":"Category parent:","field":"categoryParent","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra13","label":"Category closed:","field":"categoryClosed","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra15","label":"Meta title:","field":"metaTitle","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra16","label":"Meta description:","field":"metaDescription","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra17","label":"Hide from search:","field":"hideFromSearch","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra18","label":"Searchable keywords (Not visible to public):","field":"searchKeywords","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
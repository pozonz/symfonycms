<?php

namespace ExWife\Engine\Cms\Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\Core\Model\Model;

class NewsModel extends Model
{
    public $id = 'ab1a98fb-cc05-4320-bf8e-87f8ab670337';
   
    public $_uniqid = '2c92fd7e-6a27-4500-bc73-62579c9f3df6';
   
    public $_slug = 'news-articles';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-08 13:16:24';
   
    public $_modified = '2021-10-20 17:45:01';
   
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
   
    public $title = 'News articles';
   
    public $className = 'News';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'date';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '[]';
   
    public $frontendUrl = '/news/article/{{_slug}}';
   
    public $searchableInCms = '1';
   
    public $searchableInFrontend = '1';
   
    public $enableVersioning = '1';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"date","label":"Date:","field":"date","widget":"Date picker","required":1,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"200","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"image","label":"Image:","field":"image","widget":"Asset picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra1","label":"Hero image caption:","field":"heroCaption","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Categories:","field":"categories","widget":"Choice multi","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM NewsCategory WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":1,"listingWidth":"400","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Excerpt:","field":"excerpts","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"Featured?","field":"featured","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"100","displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"content","label":"Content blocks:","field":"contentBlocks","widget":"Content blocks","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"Related blog (max 3):","field":"relatedBlog","widget":"Choice multi","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM News WHERE `_versionOrmId` IS NULL ORDER BY `_rank`","listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"Hide from search:","field":"hideFromSearch","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra7","label":"Searchable keywords (Not visible to public):","field":"searchKeywords","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
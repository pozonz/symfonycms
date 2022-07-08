<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use SymfonyCMS\Engine\Cms\_Core\Model\Model;

class FormSubmissionModel extends Model
{
    public $id = '52cf5d6c-31c5-469a-a674-3e12f4e7f9bd';
   
    public $_uniqid = '243d4318-307d-4b0a-a4a8-4774ffb83b29';
   
    public $_slug = 'form-submissions';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-10-14 17:17:04';
   
    public $_modified = '2021-10-20 18:34:07';
   
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
   
    public $title = 'Form submissions';
   
    public $className = 'FormSubmission';
   
    public $modelCategory = '2';
   
    public $listingType = '2';
   
    public $pageSize = '200';
   
    public $defaultSortBy = 'id';
   
    public $defaultOrderBy = 'DESC';
   
    public $accesses = '["18"]';
   
    public $frontendUrl = null;
   
    public $searchableInCms = '0';
   
    public $searchableInFrontend = '0';
   
    public $enableVersioning = '0';
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":1,"chosen":false,"selected":false},{"id":"about","label":"Code:","field":"uniqueId","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"date","label":"Date:","field":"date","widget":"Date picker","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra1","label":"From address:","field":"fromAddress","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Recipients:","field":"recipients","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"Content:","field":"content","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra11","label":"Content with field:","field":"contentWithField","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"Email status:","field":"emailStatus","widget":"Choice","required":0,"unique":0,"sqlQuery":"SELECT 0 AS `key`, \'Pending\' AS value UNION \nSELECT 1 AS `key`, \'Success\' AS value UNION \nSELECT 2 AS `key`, \'Failed\' AS value ","listing":1,"listingWidth":"200","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"Email request:","field":"emailRequest","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"Email response:","field":"emailResponse","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra7","label":"Form:","field":"formDescriptorId","widget":"Choice","required":0,"unique":0,"sqlQuery":"SELECT id AS `key`, title AS value FROM FormBuilder WHERE `_versionOrmId` IS NULL ORDER BY `_rank`\n","listing":0,"listingWidth":"400","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra10","label":"Form name:","field":"formName","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":1,"listingWidth":"400","listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"url","label":"Url:","field":"url","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra8","label":"IP:","field":"ip","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra9","label":"Country:","field":"country","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
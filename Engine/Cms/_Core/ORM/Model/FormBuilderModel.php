<?php

namespace ExWife\Engine\Cms\_Core\ORM\Model;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Model\Model;

class FormBuilderModel extends Model
{
    public $id = '8ef5ce88-abeb-4b8b-8538-9d3aa8d2f92f';
   
    public $_uniqid = '2c4b2af3-d2a5-4327-9e5f-8e6feee8da23';
   
    public $_slug = 'form-builder';
   
    public $_status = '1';
   
    public $_closed = '0';
   
    public $_rank = '0';
   
    public $_added = '2021-09-03 12:30:23';
   
    public $_modified = '2021-10-20 18:33:30';
   
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
   
    public $title = 'Form builder';
   
    public $className = 'FormBuilder';
   
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
   
    public $columnsJson = '[{"id":"title","label":"Title:","field":"title","widget":"Text","required":1,"unique":1,"sqlQuery":null,"listing":1,"listingWidth":null,"queryable":1,"chosen":false,"selected":false},{"id":"extra1","label":"Code:","field":"code","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra2","label":"Form Name:","field":"formName","widget":"Hidden","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra3","label":"From address:","field":"fromAddress","widget":"Text","required":1,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra4","label":"Recipients:","field":"recipients","widget":"Textarea","required":1,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra5","label":"Use antispam?","field":"antispam","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra6","label":"Form fields:","field":"formFields","widget":"Textarea","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra8","label":"Form overview text:","field":"formOverviewText","widget":"Wysiwyg","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra9","label":"Thank you message:","field":"thankyouHeading","widget":"Wysiwyg","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra10","label":"Send thank you email:","field":"sendThankYouEmail","widget":"Checkbox","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra11","label":"Thank you email subject:","field":"thankYouEmailSubject","widget":"Text","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false},{"id":"extra12","label":"Thank you email text:","field":"thankYouEmailText","widget":"Wysiwyg","required":0,"unique":0,"sqlQuery":null,"listing":0,"listingWidth":null,"listingTitle":null,"displayFunc":null,"queryable":0,"chosen":false,"selected":false}]';
   

    public function __construct(Connection $connection)
    {
        $this->_connection = $connection;
    }
}
<?php

namespace ExWife\Engine\Cms\_Core\ORM\Generated;

use ExWife\Engine\Cms\_Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\_Core\Version\VersionInterface;
use ExWife\Engine\Cms\_Core\Version\VersionTrait;

class AssetBinaryGenerated extends BaseORM 
{
    

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz LONGBLOB NULL
     */
    public $blob;
   
}
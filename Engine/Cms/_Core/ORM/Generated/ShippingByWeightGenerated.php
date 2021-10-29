<?php

namespace ExWife\Engine\Cms\_Core\ORM\Generated;

use ExWife\Engine\Cms\_Core\Base\ORM\BaseORM;
use ExWife\Engine\Cms\_Core\Version\VersionInterface;
use ExWife\Engine\Cms\_Core\Version\VersionTrait;

class ShippingByWeightGenerated extends BaseORM 
{
    

    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $title;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $country;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $freeDeliveryIfPriceAbove;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $shippingMethod;
   
    /**
     * #pz text COLLATE utf8mb4_unicode_ci DEFAULT NULL
     */
    public $shippingCostRates;
   
}
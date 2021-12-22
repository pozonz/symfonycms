<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;

trait ContentBlockDefaultTrait
{
    /**
     * ContentBlockTrait constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->dataType = 1;
    }
}
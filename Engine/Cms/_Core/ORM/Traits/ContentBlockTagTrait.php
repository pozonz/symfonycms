<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;

trait ContentBlockTagTrait
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
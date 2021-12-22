<?php

namespace ExWife\Engine\Cms\_Core\ORM\Traits;

use Doctrine\DBAL\Connection;
use ExWife\Engine\Cms\_Core\Service\UtilsService;

trait ContentBlockTrait
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

    /**
     * @return mixed
     */
    public function objItems()
    {
        return $this->items ? json_decode($this->items) : [];
    }
}
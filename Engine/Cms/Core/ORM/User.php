<?php

namespace ExWife\Engine\Cms\Core\ORM;

use ExWife\Engine\Cms\Core\ORM\Generated\UserGenerated;
use ExWife\Engine\Cms\Core\ORM\Traits\UserTrait;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends UserGenerated implements UserInterface, EquatableInterface, \Serializable
{
    use UserTrait;
}
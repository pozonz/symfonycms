<?php

namespace ExWife\Engine\Cms\_Core\ORM;

use ExWife\Engine\Cms\_Core\ORM\Generated\UserGenerated;
use ExWife\Engine\Cms\_Core\ORM\Traits\UserTrait;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User extends UserGenerated implements UserInterface, EquatableInterface, \Serializable
{
    use UserTrait;
}
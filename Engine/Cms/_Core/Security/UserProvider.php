<?php

namespace ExWife\Engine\Cms\_Core\Security;

use Doctrine\DBAL\Connection;

use ExWife\Engine\Cms\_Core\Service\CmsService;
use ExWife\Engine\Cms\_Core\Service\UtilsService;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * UserProvider constructor.
     * @param Connection $conn
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $username
     * @return UserInterface
     */
    public function loadUserByUsername($username)
    {
        return $this->fetchUser($username);
    }

    /**
     * @param UserInterface $user
     * @return UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->fetchUser($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     * @throws \Exception
     */
    public function supportsClass($class)
    {
        $fullClass = UtilsService::getFullClassFromName('User');
        $fullClass = ltrim($fullClass, '\\');

        $class = ltrim($class, '\\');

        return $fullClass === $class;
    }

    /**
     * @param $username
     * @return mixed
     * @throws \Exception
     */
    private function fetchUser($username)
    {
        if ($username == 'NONE_PROVIDED') {
            throw new UsernameNotFoundException(
                sprintf('Please enter a username')
            );
        }

        $fullClass = UtilsService::getFullClassFromName('User');
        $user = $fullClass::getByField($this->connection, 'title', $username);

        if (!$user) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        if ($user->_status != 1) {
            throw new UsernameNotFoundException(
                sprintf('User "%s" is disabled.', $username)
            );
        }

        if (!$this->supportsClass($fullClass)) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

//        $accessibleSections = json_decode($user->accesses ?: '[]');
//        if (count($accessibleSections) === 0) {
//            throw new UnsupportedUserException(
//                sprintf('"%s" does not have any accessible sections.', $username)
//            );
//        }

        return $user;
    }
}
<?php

namespace SymfonyCMS\Engine\Cms\_Core\ORM\Traits;

use SymfonyCMS\Engine\Cms\_Core\Service\CmsService;
use SymfonyCMS\Engine\Cms\_Core\Service\UtilsService;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserTrait
{
    /**
     * @return mixed
     */
    public function objAccessibleSections()
    {
        return json_decode($this->accessibleSections ?: '[]');
    }

    /**
     * @param array $options
     * @return string|null
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function save($options = [])
    {
        if ($this->passwordInput) {
            $encoder = new MessageDigestPasswordEncoder();
            $this->password = $encoder->encodePassword($this->passwordInput, '');
            $this->passwordInput = null;
        }
        return parent::save($options);
    }

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function isEqualTo(UserInterface $user)
    {
        $fullClass = UtilsService::getFullClassFromName('User');
        if (!($user instanceof $fullClass)) {
            return false;
        }

        if ($this->getPassword() !== $user->getPassword()) {

            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }

    /**
     * @return string[]
     */
    public function getRoles()
    {
        return ['ROLE_ADMIN'];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function eraseCredentials()
    {
        return $this;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize($this->jsonSerialize());
    }

    /**
     * @param $serialized
     * @throws \Doctrine\DBAL\Exception
     */
    public function unserialize($serialized)
    {
        $obj = unserialize($serialized);
        foreach ($obj as $idx => $itm) {
            $this->$idx = $itm;
        }

        $this->_connection = \Doctrine\DBAL\DriverManager::getConnection(array(
            'url' => $_ENV['DATABASE_URL'],
        ), new \Doctrine\DBAL\Configuration());
    }
}
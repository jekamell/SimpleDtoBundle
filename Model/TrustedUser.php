<?php

namespace Mell\Bundle\SimpleDtoBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TrustedUser
 * @package Mell\Bundle\SimpleDtoBundle\Model
 */
class TrustedUser implements UserInterface
{
    private $id;
    private $name;
    private $roles;
    private $access;

    /**
     * TrustedUser constructor.
     * @param string $id
     * @param string $name
     * @param array $roles
     * @param array $access
     */
    public function __construct($id, $name, array $roles = [], array $access = [])
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('User name cannot be empty');
        }
        
        $this->id = $id;
        $this->name = $name;
        $this->roles = $roles;
        $this->access = $access;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getUsername()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array($role, $this->roles);
    }
    
    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->getId() . ' ' . $this->getName();
    }
}

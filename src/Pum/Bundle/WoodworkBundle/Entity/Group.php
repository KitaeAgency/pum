<?php

namespace Pum\Bundle\WoodworkBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ww_group")
 */
class Group
{
    public static $knownPermissions = array(
        'ROLE_WW_USERS',
        'ROLE_WW_BEAMS',
        'ROLE_WW_SCHEMA',
        'ROLE_WW_PROJECTS',
    );

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $name;

    /**
     * @ORM\Column(type="array")
     */
    protected $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="User",inversedBy="groups")
     * @ORM\JoinTable(name="ww_group_user")
     */
    protected $users;

    public function __construct()
    {
        $this->permissions = array();
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
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return Group
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Group
     */
    public function setPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!is_string($permission)) {
                throw new \InvalidArgumentException(sprintf('Expected a string, got a "%s".', is_object($permission) ? get_class($permission) : gettype($permission)));
            }
            if (!in_array($permission, self::$knownPermissions)) {
                throw new \InvalidArgumentException(sprintf('Permission "%s" unknown. Known are: %s', $permission, implode(', ', $available)));
            }
        }

        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @return array an array of strings
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}

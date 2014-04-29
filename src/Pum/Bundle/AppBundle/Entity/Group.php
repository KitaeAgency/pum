<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

/**
 * @ORM\Entity(repositoryClass="GroupRepository")
 * @ORM\Table(name="ww_group")
 */
class Group
{
    public static $knownPermissions = array(
        'ROLE_APP_CONFIG',

        'ROLE_WW_USERS',
        'ROLE_WW_BEAMS',
        'ROLE_WW_LOGS',
        'ROLE_WW_PROJECTS',

        'ROLE_PA_LIST',
        'ROLE_PA_EDIT',
        'ROLE_PA_DELETE',

        'ROLE_PA_VIEW_EDIT'
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
     * @var Permission[]
     *
     * @ORM\OneToMany(targetEntity="Permission", mappedBy="group")
     */
    protected $advancedPermissions;

    /**
     * @ORM\ManyToMany(targetEntity="User",mappedBy="groups")
     */
    protected $users;

    public function __construct($name = null)
    {
        $this->name                = $name;
        $this->permissions         = array();
        $this->users               = new ArrayCollection();
        $this->advancedPermissions = new ArrayCollection();
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
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

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

    /**
     * READ-ONLY (inverse-side of a many-to-many relation)
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param Permission[] $advancedPermissions
     */
    public function setAdvancedPermissions($advancedPermissions)
    {
        $this->advancedPermissions = $advancedPermissions;
    }

    /**
     * @return Permission[]
     */
    public function getAdvancedPermissions()
    {
        return $this->advancedPermissions;
    }

    /**
     * @param Permission $advancedPermission
     */
    public function addAdvancedPermission(Permission $advancedPermission)
    {
        $this->advancedPermissions->add($advancedPermission);
    }

    /**
     * @param Permission $advancedPermission
     * @return bool Whether or not the element was successfully removed
     */
    public function removeAdvancedPermission(Permission $advancedPermission)
    {
        return $this->advancedPermissions->removeElement($advancedPermission);
    }
}

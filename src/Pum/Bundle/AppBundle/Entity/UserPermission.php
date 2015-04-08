<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

/**
 * @ORM\Entity(repositoryClass="UserPermissionRepository")
 * @ORM\Table(name="ww_user_permission")
 */
class UserPermission extends Permission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="advancedPermissions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $user->user;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    //Implements sleep so that it does not serialize $objectPermissions
    function __sleep()
    {
        return array('id', 'user', 'attribute', 'project', 'beam', 'object', 'instance');
    }
}

<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

/**
 * @ORM\Entity(repositoryClass="GroupPermissionRepository")
 * @ORM\Table(name="ww_permission")
 */
class GroupPermission extends Permission
{
    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="advancedPermissions")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    protected $group;

    /**
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    //Implements sleep so that it does not serialize $objectPermissions
    function __sleep()
    {
        return array('id', 'group', 'attribute', 'project', 'beam', 'object', 'instance');
    }
}

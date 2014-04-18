<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

class PermissionRepository extends EntityRepository
{
    public function hasProjectPermission(Group $group, $attribute, Project $project)
    {
        return $this->findOneBy(
            array('group' => $group),
            array('attribute' => $attribute),
            array('project' => $project)
        );
    }

    public function hasBeamPermission($group, $attribute, Project $project, Beam $beam)
    {

    }

    public function hasObjectPermission($group, $attribute, Project $project, Beam $beam, ObjectDefinition $objectDefinition)
    {

    }

    public function hasInstancePermission($group, $attribute, Project $project, Beam $beam, ObjectDefinition $objectDefinition, $instance)
    {

    }


}

<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

class PermissionRepository extends EntityRepository
{
    const PERMISSION_CLASS = 'Pum\Bundle\AppBundle\Entity\Permission';

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->orderBy('u.group', 'ASC')));
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function save(Permission $permission)
    {
        $em = $this->getEntityManager();
        $em->persist($permission);
        $em->flush();
    }

    public function delete(Permission $permission)
    {
        $em = $this->getEntityManager();
        $em->remove($permission);
        $em->flush();
    }

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

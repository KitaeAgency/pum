<?php

namespace Pum\Bundle\AppBundle\Extension\Permission\Entity;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\AppBundle\Entity\PermissionRepository;
use Pum\Core\Definition\ObjectDefinition;
use Doctrine\ORM\QueryBuilder;

/**
 * A PermissionSchema.
 */
class PermissionEntityHandler
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var PumContext
     */
    protected $pumContext;

    /**
     * Constructor.
     */
    public function __construct(PumContext $pumContext)
    {
        $this->pumContext = $pumContext;
    }

    /*
     * Return QueryBuilder
     */
    public function applyPermissions(QueryBuilder $qb, ObjectDefinition $object)
    {
        if (!$ids = $this->getPermissionRepository()->getInstancesPermissions($this->getUser(), $this->pumContext->getProject(), $object->getBeam(), $object)) {
            return $qb;
        }

        return $qb
            ->andWhere($qb->expr()->in('o.id', ':p_ids'))
            ->setParameter('p_ids', $ids)
        ;
    }

    /*
     * Return PermissionRepository
     */
    protected function getPermissionRepository()
    {
        return $this->pumContext->getContainer()->get('pum.permission_repository');
    }

    /*
     * Return User
     */
    protected function getUser()
    {
        if (null === $this->user) {
            return $this->user = $this->pumContext->getContainer()->get('security.context')->getToken()->getUser();
        }

        return $this->user;
    }

    /*
     * Return PermissionEntityHandler
     */
    protected function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }
}

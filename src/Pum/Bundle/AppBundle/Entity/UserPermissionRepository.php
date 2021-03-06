<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Pagerfanta\Pagerfanta;

class UserPermissionRepository extends EntityRepository
{
    const PERMISSION_CLASS = 'Pum\Bundle\AppBundle\Entity\UserPermission';

    public function getUserPermissions(User $user, $withInstance = true)
    {
        if (!$user->getGroup()) {
            return $user->getAdvancedPermissions();
        }

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('Pum\Bundle\AppBundle\Entity\UserPermission', 'permission');
        $rsm->addJoinedEntityResult('Pum\Bundle\AppBundle\Entity\User', 'user', 'permission', 'user');
        $rsm->addJoinedEntityResult('Pum\Core\Definition\Project', 'project', 'permission', 'project');
        $rsm->addJoinedEntityResult('Pum\Core\Definition\Beam', 'beam', 'permission', 'beam');
        $rsm->addJoinedEntityResult('Pum\Core\Definition\ObjectDefinition', 'object', 'permission', 'object');
        $rsm->addFieldResult('permission', 'id', 'id');
        $rsm->addFieldResult('permission', 'attribute', 'attribute');
        $rsm->addFieldResult('user', 'user_id', 'id');
        $rsm->addFieldResult('project', 'project_id', 'id');
        $rsm->addFieldResult('beam', 'beam_id', 'id');
        $rsm->addFieldResult('object', 'object_id', 'id');
        $rsm->addFieldResult('permission', 'instance_id', 'instance');

        $queryString = "
                SELECT  (@cnt := @cnt + 1) as id,
                        p.project_id,
                        p.beam_id,
                        p.object_id,
                        p.attribute,
                        p.instance_id,
                        :user as user_id
                FROM (
                    SELECT
                        g.project_id AS project_id,
                        g.beam_id AS beam_id,
                        g.object_id AS object_id,
                        g.attribute AS attribute,
                        g.instance_id AS instance_id
                    FROM  `ww_permission` g
                    WHERE g.group_id = :group
                    UNION ALL
                    SELECT
                        u.project_id,
                        u.beam_id,
                        u.object_id,
                        u.attribute,
                        u.instance_id
                    FROM  `ww_user_permission` u
                    WHERE u.user_id = :user
                ) AS p
                CROSS JOIN (SELECT @cnt := 0) AS dummy
                GROUP BY p.project_id, p.beam_id, p.object_id, p.attribute, p.instance_id
            ";

        $query = $this->_em->createNativeQuery($queryString, $rsm);
        $query->setParameters(array(
            'user' => $user,
            'group' => $user->getGroup()
        ));

        return $query->getResult();
    }

    public function getPage($page = 1)
    {
        $page = max(1, (int) $page);

        $pager = new Pagerfanta(new DoctrineORMAdapter($this->createQueryBuilder('u')->orderBy('u.id', 'ASC')));
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function get(User $user, Project $project, Beam $beam = null, ObjectDefinition $object = null, $instance = null)
    {
        $query = $this->createQueryBuilder('p')
           ->where('p.user = :user')
           ->andWhere('p.project = :project')
           ->setParameters(array(
               ':user' => $user,
               ':project' => $project,
           ));

        if ($beam === null) {
            $query->andWhere('p.beam IS NULL');
        } else {
            $query->andWhere('p.beam = :beam')->setParameter(':beam', $beam);
        }

        if ($object === null) {
            $query->andWhere('p.object IS NULL');
        } else {
            $query->andWhere('p.object = :object')->setParameter(':object', $object);
        }

        if ($instance === null) {
            $query->andWhere('p.instance IS NULL');
        } else {
            $query->andWhere('p.instance = :instance')->setParameter(':instance', $instance);
        }

        $permissions = $query
           ->getQuery()
           ->getResult();

        if (!empty($permissions)) {
            return reset($permissions);
        }
        return null;
    }

    public function save(UserPermission $userPermission)
    {
        $p = $this->get(
            $userPermission->getUser(),
            $userPermission->getProject(),
            $userPermission->getBeam(),
            $userPermission->getObject(),
            $userPermission->getInstance()
        );

        if (null === $p || $p->getAttribute() != $userPermission->getAttribute()) {
            $em = $this->getEntityManager();
            $em->persist($userPermission);
            $em->flush();
        }
    }

    public function delete(UserPermission $userPermission)
    {
        $em = $this->getEntityManager();
        $em->remove($userPermission);
        $em->flush();
    }

    public function flush()
    {
        $em = $this->getEntityManager();
        $em->flush();
    }

    public function addPermission($attributes, $user, $project, $beam = null, $object = null, $instance = null)
    {
        // Faster but only work for Doctrine 2.5 Beta for now
        /*$qb = $this->createQueryBuilder('p');
        $qb
            ->insert()
            ->values(array(
                'attribute' => ':attribute',
                'user'     => ':user',
                'project'   => ':project',
                'beam'      => ':beam',
                'object'    => ':object',
                'instance'  => ':instance'
            ))
            ->setParameters(array(
                'attribute' => $attribute,
                'user'     => $user,
                'project'   => $project,
                'beam'      => $beam,
                'object'    => $object,
                'instance'  => $instance
            ))
            ->getQuery()
            ->execute()
        ;*/


        if (!is_array($attributes)) {
            $attributes = array($attributes);
        }

        $em = $this->getEntityManager();
        $userPermission = $this->get(
            $em->getReference('Pum\Bundle\AppBundle\Entity\User', $user),
            $em->getReference('Pum\Core\Definition\Project', $project),
            (null === $beam ? null : $em->getReference('Pum\Core\Definition\Beam', $beam)),
            (null === $object ? null : $em->getReference('Pum\Core\Definition\ObjectDefinition', $object)),
            $instance
        );

        if (!$userPermission) {
            $userPermission = new UserPermission();
            $userPermission
                ->setUser($em->getReference('Pum\Bundle\AppBundle\Entity\User', $user))
                ->setProject($em->getReference('Pum\Core\Definition\Project', $project))
                ->setBeam((null === $beam) ? null : $em->getReference('Pum\Core\Definition\Beam', $beam))
                ->setObject((null === $object) ? null : $em->getReference('Pum\Core\Definition\ObjectDefinition', $object))
                ->setInstance($instance)
            ;
        }

        $userPermission->setAttributes($attributes);
        $em->persist($userPermission);
    }

    public function deletePermissions($user, $project, $beam = null, $object = null, $instance = null, $deleteCurrentLevel = false)
    {
        $qb = $this->createQueryBuilder('p');

        $qb
            ->delete()
            ->andWhere($qb->expr()->eq('p.user', ':user'))
            ->andWhere($qb->expr()->eq('p.project', ':project'))
            ->setParameter('user', $user)
            ->setParameter('project', $project)
        ;

        if ($beam) {
            $qb
                ->andWhere($qb->expr()->eq('p.beam', ':beam'))
                ->setParameter('beam', $beam)
            ;

            if ($object) {
                $qb
                    ->andWhere($qb->expr()->eq('p.object', ':object'))
                    ->setParameter('object', $object)
                ;

                if ($instance) {
                    $qb
                        ->andWhere($qb->expr()->eq('p.instance', ':instance'))
                        ->setParameter('instance', $instance)
                    ;
                } elseif (false === $deleteCurrentLevel) {
                    $qb->andWhere($qb->expr()->isNotNull('p.instance'));
                }

            } elseif (false === $deleteCurrentLevel) {
                $qb->andWhere($qb->expr()->isNotNull('p.object'));
            }

        } elseif (false === $deleteCurrentLevel) {
            $qb->andWhere($qb->expr()->isNotNull('p.beam'));
        }

        $qb
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteByIds($ids)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->delete()
            ->andWhere($qb->expr()->in('p.id', ':ids'))
            ->setParameters(array(
                'ids' => $ids,
            ))
            ->getQuery()
            ->execute()
        ;
    }
}

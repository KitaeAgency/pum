<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
 */
class SearchApi
{
    const PROJECT_CLASS = 'Pum\Core\Definition\Project';
    const BEAM_CLASS    = 'Pum\Core\Definition\Beam';
    const OBJECT_CLASS  = 'Pum\Core\Definition\ObjectDefinition';
    const GROUP_CLASS   = 'Pum\Bundle\AppBundle\Entity\Group';
    const USER_CLASS    = 'Pum\Bundle\AppBundle\Entity\User';

    const SEARCH_TYPE_PROJECT = 'project';
    const SEARCH_TYPE_BEAM    = 'beam';
    const SEARCH_TYPE_OBJECT  = 'object';
    const SEARCH_TYPE_GROUP   = 'group';
    const SEARCH_TYPE_USER    = 'user';
    const SEARCH_TYPE_ALL     = 'all';

    public static $searchTypes = array(
        self::SEARCH_TYPE_PROJECT,
        self::SEARCH_TYPE_BEAM,
        self::SEARCH_TYPE_OBJECT,
        self::SEARCH_TYPE_GROUP,
        self::SEARCH_TYPE_USER,
        self::SEARCH_TYPE_ALL,
    );

    /**
     * @var EntityManager 
     */
    protected $em;

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    public function __construct(EntityManager $em, SecurityContextInterface $securityContext)
    {
        $this->em              = $em;
        $this->securityContext = $securityContext;
    }

    public function search($q = null, $type = self::SEARCH_TYPE_ALL, $responseType = 'JSON')
    {
        if (!in_array($type, self::$searchTypes)) {
            throw new \InvalidArgumentException(sprintf('Search type "%s" unknown. Known are: %s', $type, implode(', ', self::$searchTypes)));
        }

        if (!$q || strlen($q) < 2) {
            return new JsonResponse();
        }

        if (self::SEARCH_TYPE_ALL === $type) {
            $types = self::$searchTypes;
            array_pop($types);
        } else {
            $types = (array)$type;
        }

        $results = array();

        foreach ($types as $type) {
            switch ($type) {
                case self::SEARCH_TYPE_PROJECT:
                    if ($res = $this->searchProjects($q)) {
                        $results[$type] = $res;
                    }
                    break;
                
                case self::SEARCH_TYPE_BEAM:
                    if ($res = $this->searchBeams($q)) {
                        $results[$type] = $res;
                    }
                    break;

                case self::SEARCH_TYPE_OBJECT:
                    if ($res = $this->searchObjects($q)) {
                        $results[$type] = $res;
                    }
                    break;

                case self::SEARCH_TYPE_GROUP:
                    if ($res = $this->searchGroups($q)) {
                        $results[$type] = $res;
                    }
                    break;

                case self::SEARCH_TYPE_USER:
                    if ($res = $this->searchUsers($q)) {
                        $results[$type] = $res;
                    }
                    break;
            }
        }

        switch ($responseType) {
            case 'JSON':
                return new JsonResponse($results);

            default:
                return $results;
        }
    }

    protected function searchProjects($q)
    {
        if (false === $this->securityContext->isGranted('ROLE_WW_PROJECTS')) {
            return null;
        }

        if (!$res = $this->getItems(self::PROJECT_CLASS, $q, array('name'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['name'],
                'path' => '',
            );
        }

        return $results;
    }

    protected function searchBeams($q)
    {
        if (false === $this->securityContext->isGranted('ROLE_WW_BEAMS')) {
            return null;
        }

        if (!$res = $this->getItems(self::BEAM_CLASS, $q, array('name', 'alias'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['name'],
                'path' => '',
            );
        }

        return $results;
    }

    protected function searchObjects($q)
    {
        if (false === $this->securityContext->isGranted('ROLE_WW_BEAMS')) {
            return null;
        }

        if (!$res = $this->getItems(self::OBJECT_CLASS, $q, array('name', 'alias'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['name'],
                'path' => '',
            );
        }

        return $results;
    }

    protected function searchGroups($q)
    {
        if (false === $this->securityContext->isGranted('ROLE_WW_USERS')) {
            return null;
        }

        if (!$res = $this->getItems(self::GROUP_CLASS, $q, array('name', 'alias'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['name'],
                'path' => '',
            );
        }

        return $results;
    }

    protected function searchUsers($q)
    {
        if (false === $this->securityContext->isGranted('ROLE_WW_USERS')) {
            return null;
        }

        if (!$res = $this->getItems(self::USER_CLASS, $q, array('username', 'fullname'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['fullname'],
                'path' => '',
            );
        }

        return $results;
    }

    protected function getItems($class, $q, array $fields = array())
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('o')
            ->from($class, 'o')
        ;

        foreach ($fields as $field) {
            $qb
                ->andWhere($qb->expr()->like('o.'.$field, ':'.$field))
                ->setParameter($field, '%'.$q.'%')
            ;
        }

        if (!$res = $qb->getQuery()->getArrayResult()) {
            return null;
        }

        return $res;
    }
}

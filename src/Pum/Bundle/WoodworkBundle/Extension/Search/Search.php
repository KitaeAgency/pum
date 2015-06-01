<?php

namespace Pum\Bundle\WoodworkBundle\Extension\Search;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
 */
class Search implements SearchInterface
{
    const DEFAULT_LIMIT   = 10;

    const SEARCH_TYPE_PROJECT = 'project';
    const SEARCH_TYPE_BEAM    = 'beam';
    const SEARCH_TYPE_OBJECT  = 'object';
    const SEARCH_TYPE_GROUP   = 'group';
    const SEARCH_TYPE_USER    = 'user';
    const SEARCH_TYPE_ALL     = 'all';

    const PROJECT_CLASS = 'Pum\Core\Definition\Project';
    const BEAM_CLASS    = 'Pum\Core\Definition\Beam';
    const OBJECT_CLASS  = 'Pum\Core\Definition\ObjectDefinition';
    const GROUP_CLASS   = 'Pum\Bundle\AppBundle\Entity\Group';
    const USER_CLASS    = 'Pum\Bundle\AppBundle\Entity\User';

    const PROJECT_CSS_CLASS = 'pomegranate';
    const BEAM_CSS_CLASS    = 'belizehole';
    const OBJECT_CSS_CLASS  = 'grass';
    const GROUP_CSS_CLASS   = 'carrot';
    const USER_CSS_CLASS    = 'concrete';

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
     * @var authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(EntityManager $em, AuthorizationChecker $authorizationChecker, UrlGeneratorInterface $urlGenerator)
    {
        $this->em                   = $em;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator         = $urlGenerator;
    }

    public function search($q, $type, $limit, $page)
    {
        if (!in_array($type, self::$searchTypes)) {
            throw new \InvalidArgumentException(sprintf('Search type "%s" unknown. Known are: %s', $type, implode(', ', self::$searchTypes)));
        }

        if (!$q || strlen($q) < 2) {
            $res = array();
        } else {
            $method = 'search'.ucfirst($type);
            $res    = $this->$method($q, $limit, $page);
            $res    = $this->setAttributes($res);
        }

        return new JsonResponse($res);
    }

    protected function searchAll($q, $limit, $page)
    {
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $rsm
            ->addScalarResult('uid', 'id')
            ->addScalarResult('name', 'name')
            ->addScalarResult('label', 'label')
            ->addScalarResult('type', 'type')
            ->addScalarResult('beamName', 'beamName')
        ;

        $queryString = "
                SELECT
                    o.uid,
                    o.name,
                    CASE
                        WHEN o.alias IS NULL THEN o.name
                        WHEN o.alias IS NOT NULL THEN o.alias
                    END as 'label',
                    o.type,
                    o.beamName
                FROM (
                    SELECT
                        p.id AS uid,
                        p.name AS name,
                        p.name AS alias,
                        NULL AS beamName,
                        '".self::SEARCH_TYPE_PROJECT."' AS type
                    FROM `schema_project` p
                    WHERE p.name LIKE :q
                    UNION ALL

                    SELECT
                        b.id AS uid,
                        b.name AS name,
                        b.alias AS alias,
                        NULL AS beamName,
                        '".self::SEARCH_TYPE_BEAM."' AS type
                    FROM `schema_beam` b
                    WHERE b.name LIKE :q
                    OR b.alias LIKE :q
                    UNION ALL

                    SELECT
                        ob.id AS uid,
                        ob.name AS name,
                        ob.alias AS alias,
                        beam.name AS beamName,
                        '".self::SEARCH_TYPE_OBJECT."' AS type
                    FROM `schema_object` ob
                    INNER JOIN `schema_beam` beam ON ob.schema_beam_id = beam.id
                    WHERE ob.name LIKE :q
                    OR ob.alias LIKE :q
                    UNION ALL

                    SELECT
                        g.id AS uid,
                        g.name AS name,
                        g.alias AS alias,
                        NULL AS beamName,
                        '".self::SEARCH_TYPE_GROUP."' AS type
                    FROM `ww_group` g
                    WHERE g.name LIKE :q
                    OR g.alias LIKE :q
                    UNION ALL

                    SELECT
                        u.id AS uid,
                        u.username AS name,
                        u.fullname AS alias,
                        NULL AS beamName,
                        '".self::SEARCH_TYPE_USER."' AS type
                    FROM `ww_user` u
                    WHERE u.username LIKE :q
                    OR u.fullname LIKE :q
                ) AS o
                ORDER BY label ASC
                LIMIT ".$limit."
                OFFSET ".($page-1) * $limit."
            ";

        $query = $this->em->createNativeQuery($queryString, $rsm);
        $query->setParameters(array(
            'q' => '%'.$q.'%'
        ));

        return $query->getResult();
    }

    protected function searchProject($q, $limit, $page)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_PROJECTS')) {
            return null;
        }

        if (!$res = $this->getItems(self::PROJECT_CLASS, $q, array('name'), $limit, $page)) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'    => $v['id'],
                'name'  => $v['name'],
                'label' => $v['name'],
                'type'  => self::SEARCH_TYPE_PROJECT
            );
        }

        return $results;
    }

    protected function searchBeam($q, $limit, $page)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_BEAMS')) {
            return null;
        }

        if (!$res = $this->getItems(self::BEAM_CLASS, $q, array('name', 'alias'), $limit, $page)) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'    => $v['id'],
                'name'  => $v['name'],
                'label' => $v['alias'] ? $v['alias'] : $v['name'],
                'type'  => self::SEARCH_TYPE_BEAM
            );
        }

        return $results;
    }

    protected function searchObject($q, $limit, $page)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_BEAMS')) {
            return null;
        }

        if (!$res = $this->getItems(self::OBJECT_CLASS, $q, array('name', 'alias'), $limit, $page)) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'       => $v['id'],
                'name'     => $v['name'],
                'label'    => $v['alias'] ? $v['alias'] : $v['name'],
                'beamName' => $v['beam']['name'],
                'type'     => self::SEARCH_TYPE_OBJECT
            );
        }

        return $results;
    }

    protected function searchGroup($q, $limit, $page)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_USERS')) {
            return null;
        }

        if (!$res = $this->getItems(self::GROUP_CLASS, $q, array('name', 'alias'), $limit, $page)) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'    => $v['id'],
                'name'  => $v['name'],
                'label' => $v['alias'] ? $v['alias'] : $v['name'],
                'type'  => self::SEARCH_TYPE_GROUP
            );
        }

        return $results;
    }

    protected function searchUser($q, $limit, $page)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_USERS')) {
            return null;
        }

        if (!$res = $this->getItems(self::USER_CLASS, $q, array('username', 'fullname'), $limit, $page)) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'    => $v['id'],
                'name'  => $v['username'],
                'label' => $v['fullname'],
                'type'  => self::SEARCH_TYPE_USER
            );
        }

        return $results;
    }

    protected function getItems($class, $q, array $fields, $limit, $page)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('o')
            ->from($class, 'o')
        ;

        foreach ($fields as $field) {
            $qb
                ->orWhere($qb->expr()->like('o.'.$field, '?'.$field))
                ->setParameter($field, '%'.$q.'%')
            ;
        }

        if (self::OBJECT_CLASS === $class) {
            $qb
                ->addSelect('partial b.{id,name}')
                ->leftJoin('o.beam', 'b')
            ;
        }

        $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset = ($page-1) * $limit)
        ;

        if (!$res = $qb->getQuery()->getArrayResult()) {
            return array();
        }

        return $res;
    }

    protected function setAttributes($items)
    {
        foreach ($items as $k => $v) {
            switch ($v['type']) {
                case self::SEARCH_TYPE_PROJECT:
                    $items[$k]['class'] = self::PROJECT_CSS_CLASS;
                    $items[$k]['path']  = $this->urlGenerator->generate('ww_project_edit', array(
                        'projectName' => $v['name'],
                    ));
                    break;

                case self::SEARCH_TYPE_BEAM:
                    $items[$k]['class'] = self::BEAM_CSS_CLASS;
                    $items[$k]['path']  = $this->urlGenerator->generate('ww_beam_edit', array(
                        'beamName' => $v['name'],
                    ));
                    break;

                case self::SEARCH_TYPE_OBJECT:
                    $items[$k]['class'] = self::OBJECT_CSS_CLASS;
                    $items[$k]['path']  = $this->urlGenerator->generate('ww_object_definition_edit', array(
                        'beamName' => $v['beamName'],
                        'name'     => $v['name'],
                    ));
                    break;

                case self::SEARCH_TYPE_GROUP:
                    $items[$k]['class'] = self::GROUP_CSS_CLASS;
                    $items[$k]['path']  = $this->urlGenerator->generate('ww_group_edit', array(
                        'id' => $v['id'],
                    ));
                    break;

                case self::SEARCH_TYPE_USER:
                    $items[$k]['class'] = self::USER_CSS_CLASS;
                    $items[$k]['path']  = $this->urlGenerator->generate('ww_user_edit', array(
                        'id' => $v['id'],
                    ));
                    break;
            }

            unset($items[$k]['id']);
            unset($items[$k]['name']);
            unset($items[$k]['beamName']);
        }

        return $items;
    }
}

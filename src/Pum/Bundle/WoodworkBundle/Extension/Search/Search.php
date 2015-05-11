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
class Search extends AbstractSearch
{
    const PROJECT_CLASS = 'Pum\Core\Definition\Project';
    const BEAM_CLASS    = 'Pum\Core\Definition\Beam';
    const OBJECT_CLASS  = 'Pum\Core\Definition\ObjectDefinition';
    const GROUP_CLASS   = 'Pum\Bundle\AppBundle\Entity\Group';
    const USER_CLASS    = 'Pum\Bundle\AppBundle\Entity\User';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var authorizationChecker
     */
    protected $authorizationChecker;

    public function __construct(EntityManager $em, AuthorizationChecker $authorizationChecker, UrlGeneratorInterface $urlGenerator)
    {
        $this->em                   = $em;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator         = $urlGenerator;
    }

    public function search($q, $type, $responseType)
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
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_PROJECTS')) {
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
                'type' => self::SEARCH_TYPE_PROJECT,
                'path' => $this->urlGenerator->generate('ww_project_edit', array(
                    'projectName' => $v['name'],
                )),
            );
        }

        return $results;
    }

    protected function searchBeams($q)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_BEAMS')) {
            return null;
        }

        if (!$res = $this->getItems(self::BEAM_CLASS, $q, array('name', 'alias'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['alias'] ? $v['alias'] : $v['name'],
                'type' => self::SEARCH_TYPE_BEAM,
                'path' => $this->urlGenerator->generate('ww_beam_edit', array(
                    'beamName' => $v['name'],
                )),
            );
        }

        return $results;
    }

    protected function searchObjects($q)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_BEAMS')) {
            return null;
        }

        if (!$res = $this->getItems(self::OBJECT_CLASS, $q, array('name', 'alias'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['alias'] ? $v['alias'] : $v['name'],
                'type' => self::SEARCH_TYPE_OBJECT,
                'path' => $this->urlGenerator->generate('ww_object_definition_edit', array(
                    'beamName' => $v['beam']['name'],
                    'name'     => $v['name'],
                )),
            );
        }

        return $results;
    }

    protected function searchGroups($q)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_USERS')) {
            return null;
        }

        if (!$res = $this->getItems(self::GROUP_CLASS, $q, array('name', 'alias'))) {
            return $res;
        }

        $results = array();
        foreach ($res as $k => $v) {
            $results[] = array(
                'id'   => $v['id'],
                'name' => $v['alias'] ? $v['alias'] : $v['name'],
                'type' => self::SEARCH_TYPE_GROUP,
                'path' => $this->urlGenerator->generate('ww_group_edit', array(
                    'id' => $v['id'],
                )),
            );
        }

        return $results;
    }

    protected function searchUsers($q)
    {
        if (false === $this->authorizationChecker->isGranted('ROLE_WW_USERS')) {
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
                'type' => self::SEARCH_TYPE_USER,
                'path' => $this->urlGenerator->generate('ww_user_edit', array(
                    'id' => $v['id'],
                )),
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
                ->orWhere($qb->expr()->like('o.'.$field, ':'.$field))
                ->setParameter($field, '%'.$q.'%')
            ;
        }

        if (self::OBJECT_CLASS === $class) {
            $qb
                ->addSelect('partial b.{id,name}')
                ->leftJoin('o.beam', 'b')
            ;
        }

        if (!$res = $qb->getQuery()->getArrayResult()) {
            return null;
        }

        return $res;
    }
}

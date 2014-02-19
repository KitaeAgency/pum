<?php

namespace Pum\Core\Extension\View\Entity;

use Pum\Bundle\CoreBundle\PumContext;

class Entity
{
    private $context;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    public function getRepository($objectName)
    {
        return $this->context->getProjectOEM()->getRepository($objectName);
    }

    public function getEntity($objectName, $id)
    {
        return $this->getRepository($objectName)->find($id);
    }

    public function getEntities($objectName, $criterias, $orderBy, $limit, $offset, $debug = false)
    {
        $qb = $this->getRepository($objectName)->createQueryBuilder('o');

        if (count($criterias) == 1) {
            $criterias = array($criterias);
        }

        foreach ($criterias as $k => $where) {
            foreach ($where as $key => $data) {
                $data     = (array)$data;
                $value    = (isset($data[0])) ? $data[0] : null;
                $operator = (isset($data[1])) ? $data[1] : "eq";
                $method   = (isset($data[2])) ? $data[2] : "andWhere";

                if (!in_array($method, array("andWhere", "orWhere"))) {
                    $method = "andWhere";
                }
                if (!in_array($operator, array("andX", "orX", "eq", "gt", "lt", "lte", "gte", "neq", "isNull", "in", "notIn"))) {
                    $operator = "eq";
                }

                $qb
                    ->$method($qb->expr()->$operator($key, ':'.$k))
                    ->setParameter($k, $value)
                ;
            }
        }

        if (!empty($orderBy)) {
            foreach ($orderBy as $sort => $order) {
                if (!in_array($order = strtoupper($order), array('ASC', 'DESC'))) {
                    $order = 'ASC';
                }
                $qb->orderBy('o.'.$order, $order);
            }
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (false === $debug) {
            return $qb->getQuery()->getResult();
        } else {
            return $qb->getQuery()->getDQL();
        }
    }

    public function getEntitiesDebug($objectName, $criterias, $orderBy, $limit, $offset)
    {
        return $this->getEntities($objectName, $criterias, $orderBy, $limit, $offset, $debug = true);
    }

}

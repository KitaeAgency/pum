<?php

namespace Pum\Core\Object;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\FieldDefinition;

class ObjectRepository extends EntityRepository
{

    /**
     * Searches through text.
     *
     * @return array
     */
    public function getSearchResult($q, QueryBuilder $qb = null, $fieldName = null, $limit = null, $offset = null)
    {
        if ($qb === null) {
            $qb = $this->createQueryBuilder('o');
        }

        if (null !== $fieldName) {
            if ($q) {
                $qb
                    ->where('o.'.$fieldName.' LIKE :q')
                    ->setParameter('q', '%'.$q.'%')
                ;
            }

            if (null !== $limit) {
                $qb->setMaxResults($limit);
            }

            if (null !== $offset) {
                $qb->setFirstResult($offset);
            }

            return $qb->getQuery()->execute();
        }

        $possibleFields = array('name', 'title', 'firstname', 'lastname', 'username', 'description');
        $metadata       = $this->getClassMetadata();

        foreach ($possibleFields as $name) {
            if ($metadata->hasField($name)) {
                if ($q) {
                    $qb
                        ->where('o.'.$name.' LIKE :q')
                        ->setParameter('q', '%'.$q.'%')
                    ;
                }

                return $qb->getQuery()->execute();
            }
        }

        throw new \RuntimeException(sprintf('Unable to guess where to search.'));
    }

    /**
     * Searches through Ids.
     *
     * @return array
     */
    public function getResultByIds($ids, QueryBuilder $qb = null, $delimiter = '-')
    {
        $fieldName = 'id';

        if ($qb === null) {
            $qb = $this->createQueryBuilder('o');
        }

        $qb
            ->andWhere($qb->expr()->in('o.'.$fieldName, ':ids'))
            ->setParameters(array(
                'ids' => explode($delimiter, $ids)
            ))
        ;

        return $qb->getQuery()->execute();
    }

    public function getTypeHierarchyAndFieldContext($field)
    {
        $class         = $this->getClassname();
        $objectFactory = $this->_em->getObjectFactory();

        $project  = $objectFactory->getProject($class::PUM_PROJECT);
        $context  = new FieldContext($project, $field, $field->getTypeOptions());
        $features = $objectFactory->getTypeHierarchy($field->getType(), 'Pum\Core\Extension\ProjectAdmin\ProjectAdminFeatureInterface');

        return array($features, $context);
    }

    public function addOrderCriteria(QueryBuilder $qb, $field, $order)
    {
        list($features, $context) = $this->getTypeHierarchyAndFieldContext($field);

        foreach ($features as $feature) {
            $qb = $feature->addOrderCriteria($context, $qb, $order);
        }

        return $qb;
    }

    public function addFilterCriteria(QueryBuilder $qb, $field, $values)
    {
        list($features, $context) = $this->getTypeHierarchyAndFieldContext($field);

        foreach ($features as $feature) {
            $qb = $feature->addFilterCriteria($context, $qb, $values);
        }

        return $qb;
    }

    public function getPageQuery(FieldDefinition $sortField = null, $order = 'asc', $filters = array())
    {
        return $this->getPage(null, null, $sortField, $order, $filters, true);
    }

    public function getPage($page = 1, $per_page = 10, FieldDefinition $sortField = null, $order = 'asc', $filters = array(), $returnQuery = false)
    {
        $page = max(1, (int) $page);

        $qb = $this->createQueryBuilder('o');

        // Order stuff
        if (!is_null($sortField)) {
            $qb = $this->addOrderCriteria($qb, $sortField, $order);
        } else {
            $qb->orderby($qb->getRootAlias() . '.id', $order);
        }

        // Filters stuff
        if ($filters) {
            foreach ($filters as $filter) {
                foreach ($filter['filters'] as $filterObj) {
                    $qb = $this->addFilterCriteria($qb, $filter['field'], array(
                        'type'  => $filterObj->getType(),
                        'value' => $filterObj->getValue()
                    ));
                }
            }
        }

        if ($returnQuery) {
            return $qb;
        }

        $adapter = new DoctrineORMAdapter($qb);
        $pager   = new Pagerfanta($adapter);

        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        return $pager;
    }

    public function getObjectsBy(array $criteria = array(), $orderBy = null, $limit = null, $offset = null, $returnQuery = false)
    {
        $qb = $this->createQueryBuilder('o');

        $i = 0;
        $parameters = array();

        foreach ($criteria as $key => $value) {
            $i++;
            if (null !== $value) {
                $qb->andWhere('o.'.$key.' = :'.$key.$i);
                $parameters[$key.$i] = $value;
            } else {
                $qb->andWhere('o.'.$key.' IS NULL');
            }
        }

        if ($parameters) {
            $qb->setParameters($parameters);
        }

        if (null != $orderBy) {
            if (is_string($orderBy)) {
                $qb->orderBy('o.'.$orderBy, "ASC");
            } elseif (is_array($orderBy)) {
                foreach ($orderBy as $key => $type) {
                    if (!in_array(strtoupper($type), array('ASC', 'DESC'))) {
                        $type = 'ASC';
                    }

                    $qb->orderBy('o.'.$key, strtoupper($type));
                }
            }
        }
        
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }

        if ($returnQuery) {
            return $qb;
        }

        return $qb->getQuery()->getResult();
    }

    public function countBy(array $criteria = array(), array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->getObjectsBy($criteria, $orderBy, $limit, $offset, $returnQuery = true)
            ->select('COUNT(o.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}

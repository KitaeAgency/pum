<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Object;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Relation\Relation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Pagerfanta;
use Doctrine\Common\Collections\Criteria;

class CollectionManager
{
    protected $context;

    /**
     * @param pum object
     */
    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    /**
     * @param pum object
     * @param FieldDefinition $field
     */
    public function count($object, FieldDefinition $field)
    {
        $camel    = $field->getCamelCaseName();
        $getter   = 'get'.ucfirst($camel);
        $multiple = in_array($field->getTypeOption('type'), array('one-to-many', 'many-to-many'));

        if ($multiple) {
            return $object->$getter()->count();
        } else {
            return (null === $object->$getter()) ? 0 : 1;
        }
    }

    /**
     * @param pum object
     * @param FieldDefinition $field
     */
    public function getItems($object, FieldDefinition $field, $page = 1, $byPage = 10, array $orderBy = array())
    {
        $camel    = $field->getCamelCaseName();
        $getter   = 'get'.ucfirst($camel);
        $multiple = in_array($field->getTypeOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY));

        if ($multiple) {
            /* Matching Criteria on PersistentCollection only works on OneToMany associations at the moment */
            if (in_array($field->getTypeOption('type'), array(Relation::ONE_TO_MANY))) {
                $criteria = Criteria::create();
                $criteria = $this->handleCriteria($criteria, $orderBy);
                $children = $object->$getter()->matching($criteria);
            } else {
                $children = $object->$getter();
            }

            $pager = new \Pagerfanta\Adapter\DoctrineCollectionAdapter($children);
            $pager = new Pagerfanta($pager);
            $pager = $pager->setMaxPerPage($byPage);
            $pager = $pager->setCurrentPage($page);

            return $pager;
        } else {
            return $object->$getter();
        }
    }

    /**
     * @param criteria
     * @param orderBy
     * @param limite
     * @param offset
     */
    private function handleCriteria(Criteria $criteria, array $orderBy = array(), $limit = null, $offset = null)
    {
        if (null !== $limit) {
            $criteria->setMaxResults($limit);
        }

        if (null !== $offset) {
            $criteria->setFirstResult($offset);
        }

        if (null === $orderBy || empty($orderBy)) {
            $criteria->orderBy(array('id' => Criteria::ASC));
        } else {
            $criteria->orderBy($orderBy);
        }

        return $criteria;
    }

    /**
     * @param Request request
     * @param pum object
     * @param FieldDefinition $field
     */
    public function handleRequest(Request $request, $object, FieldDefinition $field, $return = null)
    {
        if (!$action = $request->query->get('action')) {
            return;
        }

        $camel      = $field->getCamelCaseName();
        $getter     = 'get'.ucfirst($camel);
        $objectName = $field->getTypeOption('target');
        $multiple   = in_array($field->getTypeOption('type'), array('one-to-many', 'many-to-many'));
        $items      = $object->$getter();

        if (in_array($action, array('removeselected', 'remove', 'removeall', 'set', 'add'))) {

            $ids = $request->query->get('ids', $request->request->get('ids'));
            if (!is_array($ids)) {
                if ($ids) {
                    $delimiter = $request->query->get('_pum_q_delimiter', '-');
                    $ids       = explode($delimiter, $ids);
                } else {
                    $ids = array();
                }
            }

            if ($multiple) {
                $singular = Namer::getSingular($camel);

                $adder    = 'add'.ucfirst($singular);
                $remover  = 'remove'.ucfirst($singular);
            } else {
                $adder = $remover = 'set'.ucfirst($camel);
            }

            /* 
             * http://t2378.php-doctrine-user.phptalk.us/calling-removeelement-on-extra-lazy-loadedassociation-removes-element-from-database-t2378.html
             * EXTRA_LAZY throws any read/writes directly to the DB if the collection is NOT initialized.
             * Pum only use FETCH_EXTRA_LAZY for oneToMany relation for now, so we fake to initialize the collection.
             */
            if ($multiple) {
                foreach ($items as $item) {
                    break;
                }
            }

            switch ($action) {
                case 'removeselected':
                case 'remove':
                    if ($multiple) {
                        foreach ($ids as $id) {
                            if (null !== $item = $this->getRepository($objectName)->find($id)) {
                                $object->$remover($item);
                            }
                        }
                    } else {
                        $object->$remover(null);
                    }

                    break;

                case 'removeall':
                    if ($multiple) {
                        foreach ($items as $item) {
                            $object->$remover($item);
                        }
                    } else {
                        $object->$remover(null);
                    }
                    

                    break;

                case 'set':
                case 'add':
                    foreach ($ids as $id) {
                        if (null !== $item = $this->getRepository($objectName)->find($id)) {
                            $object->$adder($item);
                        }
                    }

                    break;
            }

            $this->flush();

            return $return;

        } elseif (in_array($action, array('search'))) {

            $objectName = $request->request->get('_pum_list', $request->query->get('_pum_list'));
            $q          = $request->request->get('_pum_q', $request->query->get('_pum_q'));
            $field      = $request->request->get('_pum_field', $request->query->get('_pum_field', 'id'));
            $limit      = $request->request->get('_pum_per_page', $request->query->get('_pum_per_page', 500));
            $page       = $request->request->get('_pum_page', $request->query->get('_pum_page', 1));
            $offset     = ($page-1) * $limit;

            if (!$objectName) {
                return;
            }

            $results = $this->getRepository($objectName)->getSearchResult($q, $qb = null, $field, $limit, $offset);

            foreach ($results as $key => $result) {
                if ($multiple) {
                    if ($items->contains($result)) {
                        unset($results[$key]);
                    }
                } else {
                    if ($items === $result) {
                        unset($results[$key]);
                    }
                }
            }

            $res = array_map(function ($result) use ($field, $objectName) {
                $getter = 'get'.ucfirst($field);

                switch ($field) {
                    case 'id':
                        return array(
                            'id'    => $result->getId(),
                            'value' => (string) (ucfirst($objectName).' #'.$result->$getter())
                        );
                        break;

                    default:
                        return array(
                            'id'    => $result->getId(),
                            'value' => (string) (trim($result->$getter()).' #'.$result->getId())
                        );
                        break;
                }

            }, $results);

            return new JsonResponse($res);
        }

        return;
    }

    public function getOEM()
    {
        return $this->context->getProjectOEM();
    }

    public function getRepository($objectName)
    {
        return $this->getOEM()->getRepository($objectName);
    }

    public function persist()
    {
        $objects = func_get_args();
        foreach ($objects as $object) {
            $this->getOEM()->persist($object);
        }

        return $this->getOEM();
    }

    public function remove()
    {
        $objects = func_get_args();
        foreach ($objects as $object) {
            $this->getOEM()->remove($object);
        }

        return $this->getOEM();
    }

    public function flush()
    {
        return $this->getOEM()->flush();
    }
}

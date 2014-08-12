<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Object;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Definition\FieldDefinition;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function getItems($object, FieldDefinition $field, $page = 1, $byPage = 10)
    {
        $fieldName = $field->getCamelCaseName();
        $getter    = 'get'.ucfirst($fieldName);
        $multiple  = in_array($field->getTypeOption('type'), array('one-to-many', 'many-to-many'));

        if ($multiple) {
            $children  = $object->$getter();

            $pager = new \Pagerfanta\Adapter\DoctrineCollectionAdapter($children);
            $pager = new Pagerfanta($pager);
            $pager = $pager->setMaxPerPage($byPage);
            $pager = $pager->setCurrentPage($page);

            return $pager;
        } else {
            return array();
        }
    }

    /**
     * @param Request request
     * @param pum object
     * @param FieldDefinition $field
     */
    public function handleRequest(Request $request, $object, FieldDefinition $field)
    {
        if (!$action = $request->query->get('action')) {
            return;
        }

        $ids = $request->query->get('ids');
        if ($ids) {
            $delimiter = $request->query->get('delimiter', '-');
            $ids       = explode($delimiter, $ids);
        } else {
            $ids = array();
        }

        $fieldName  = $field->getCamelCaseName();
        $objectName = $field->getTypeOption('target');
        $multiple   = in_array($field->getTypeOption('type'), array('one-to-many', 'many-to-many'));

        if ($multiple) {
            if (substr($fieldName, -1) === 's') {
                    $singular = substr($fieldName, 0, -1);
                } else {
                    $singular = $fieldName;
                }

                $adder    = 'add'.ucfirst($singular);
                $remover  = 'remove'.ucfirst($singular);
        } else {
            $adder = $remover = 'set'.ucfirst($fieldName);
        }

        

        switch ($action) {
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

                return new Response('OK');

            case 'add':
                foreach ($ids as $id) {
                    if (null !== $item = $this->getRepository($objectName)->find($id)) {var_dump($id);
                        $object->$adder($item);
                    }
                }

                $this->persist($object)->flush();

                return new Response('OK');
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

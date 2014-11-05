<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Object\Tree;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class TreeApi
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
     * @param Request request
     * @param pum object
     * @param FieldDefinition $field
     */
    public function handleRequest(Request $request, ObjectDefinition $object)
    {
        if (!$action = $request->query->get('tree_action')) {
            return;
        }

        $options = array(
            'label_field'    => $request->query->get('tree_label_field'),
            'parent_field'   => $request->query->get('tree_parent_field'),
            'children_field' => $request->query->get('tree_children_field')
        );

        $nodes = array(1,5);

        switch ($action) {
            case 'root':
                return $this->getRoots($request, $object, $options);
            break;

            case 'node':

            break;

            default:
                return;
            break;
        }
    }

    public function getOEM()
    {
        return $this->context->getProjectOEM();
    }

    public function getRepository($objectName)
    {
        return $this->getOEM()->getRepository($objectName);
    }

    private function getRoots(ObjectDefinition $object, array $options)
    {
        $rootObjects = $this->getRootObjects($object, $options);
    }

    private function getRootObjects(ObjectDefinition $object, array $options)
    {
        
    }

}

<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class TreeController extends Controller
{
    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/treeapi", name="pa_object_tree_api")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function treeApiAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        $this->assertGranted('PUM_OBJ_VIEW', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $object->getName(),
        ));

        if (false === $object->isTreeEnabled() || null === $tree = $object->getTree()) {
            throw new \RuntimeException($object->getName().' is not treeable');
        }

        if (null === $treeField = $tree->getTreeField()) {
            throw new \RuntimeException('No tree field defined for the object '.$object->getName());
        }

        $labelField = $tree->getLabelField();
        $options    = array(
            'label_field'    => $labelField ? $labelField->getName() : 'id',
            'children_field' => $treeField->getName(),
            'parent_field'   => $treeField->getTypeOption('inversed_by'),
            'node_value'     => $request->query->get('id', '#'),
            'action'         => $request->query->get('action', 'node')
        );

        /* Handle Ajax Request */
        $handler = $this->get('pum.object.tree.api');
        if ($response = $handler->handleRequest($request, $object, $options)) {
            return $response;
        }

        return new JsonResponse();
    }
}

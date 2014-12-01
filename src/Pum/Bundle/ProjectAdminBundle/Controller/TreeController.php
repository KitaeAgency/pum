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

        // Create Node
        if ($options['action'] == 'create_node') {
            $obj = $this->get('pum.oem')->createObject($object->getName());

            if ('#' == $parent = $request->query->get('parent_id', null)) {
                $parent = null;
            }

            if (null !== $parent) {
                $parentSetter = 'set'.ucfirst(Namer::toCamelCase($options['parent_field']));

                if (null !== $parent = $this->get('pum.oem')->getRepository($object->getName())->find($parent)) {
                    $obj->$parentSetter($parent);
                }
            }

            return $this->createAction($request, $beam, $object->getName(), $object, $obj);
        }

        /* Handle Ajax Request */
        $handler = $this->get('pum.object.tree.api');
        if ($response = $handler->handleRequest($request, $object, $options)) {
            return $response;
        }

        return new JsonResponse();
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/create_tree_node", name="pa_object_tree_create")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function createTreeNodeAction(Request $request, Beam $beam, $name, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('PUM_OBJ_CREATE', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
        ));

        if (false === $objectDefinition->isTreeEnabled() || null === $tree = $objectDefinition->getTree()) {
            throw new \RuntimeException($object->getName().' is not treeable');
        }

        if (null === $treeField = $tree->getTreeField()) {
            throw new \RuntimeException('No tree field defined for the object '.$objectDefinition->getName());
        }

        $oem    = $this->get('pum.context')->getProjectOEM();
        $object = $oem->createObject($name);

        if ('#' == $parent = $request->query->get('parent_id', null)) {
            $parent = null;
        }

        $form = $this->createForm('pum_object', $object, array(
            'action'    => $this->generateUrl('pa_object_tree_create', array('beamName' => $beam->getName(), 'name' => $objectDefinition->getName(), 'parent_id' => $parent)),
            'form_view' => $this->getDefaultFormView($formViewName = $request->query->get('view'), $objectDefinition)
        ));

        if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
            return $response;
        }

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if ($parent) {
                $parentSetter = 'set'.ucfirst(Namer::toCamelCase($treeField->getTypeOption('inversed_by')));

                if (null !== $parent = $oem->getRepository($objectDefinition->getName())->find($parent)) {
                    $object->$parentSetter($parent);
                }
            }

            $oem->persist($object);
            $oem->flush();

            return new JsonResponse('OK');
        }

        $params = array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $object,
        );

        return $this->render('PumProjectAdminBundle:Object:create.ajax.html.twig', $params);
    }

    /*
     * Return FormView
     * Throw createNotFoundException
     */
    private function getDefaultFormView($formViewName, ObjectDefinition $object)
    {
        if (FormView::DEFAULT_NAME === $formViewName) {
            return $object->createDefaultFormView();
        }

        if ($formViewName === null || $formViewName === '') {
            if (null !== $formView = $object->getDefaultFormView()) {
                return $formView;
            }

            return $object->createDefaultFormView();

        } else {
            try {
                $formView = $object->getFormView($formViewName);

                return $formView;
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Form view not found.', $e);
            }
        }
    }
}

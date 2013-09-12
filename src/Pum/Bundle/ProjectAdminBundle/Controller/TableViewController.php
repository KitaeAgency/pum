<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\TableView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class TableViewController extends Controller
{
    const DEFAULT_NAME = 'Default';

    /**
     * @Route(path="/{_project}/{beamName}/{name}/tableview/create", name="pa_tableview_create")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function createAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $form = $this->createForm('pa_tableview', $object->createTableView());

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);
            $this->addSuccess('TableView successfully created');

            return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $object->getName(), 'view' => $form->getData()->getName())));
        }

        return $this->render('PumProjectAdminBundle:TableView:create.html.twig', array(
            'beam' => $beam,
            'object_definition' => $object,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/tableview/{tableViewName}/edit", name="pa_tableview_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, ObjectDefinition $object, $tableViewName)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $tableView = $object->getTableView($tableViewName);
        $form = $this->createForm('pa_tableview', $tableView);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $this->get('pum')->saveBeam($beam);

            return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $object->getName(), 'view' => $tableView->getName())));
        }

        return $this->render('PumProjectAdminBundle:TableView:edit.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $object,
            'table_view'        => $tableView,
            'form'              => $form->createView()
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/tableview/{tableViewName}/delete", name="pa_tableview_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function deleteAction(Beam $beam, ObjectDefinition $object, $tableViewName)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');
        
        $object->removeTableView($object->getTableView($tableViewName));
        $this->get('pum')->saveBeam($beam);
        $this->addSuccess('TableView successfully deleted');

        return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $object->getName())));
    }
}

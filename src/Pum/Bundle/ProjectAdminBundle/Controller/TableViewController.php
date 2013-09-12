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

        $form = $this->createForm('ww_tableview');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $object->createDefaultTableView($form->getData()->getName());
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

        if ($request->isMethod('POST')) {
            $tableView->configure($request);
            $this->get('pum')->saveBeam($beam);

            return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $object->getName(), 'view' => $tableView->getName())));
        }

        return $this->render('PumProjectAdminBundle:TableView:edit.html.twig', array(
            'beam' => $beam,
            'object_definition' => $object,
            'tableView' => $tableView,
            'filterPrototype' => $this->getFilterObjectDataPrototype($object)
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

    /*
     * @return prototype for filter
     */ 
    private function getFilterObjectDataPrototype(ObjectDefinition $object)
    {
        $prototype = '<td class="col-lg-6">';
        $prototype .= '<select name="filters[columns][__random_key__]" class="form-control filter-type" data-key="__random_key__" data-url="' .$this->generateUrl('ww_ajax_filter_type'). '">';
        $prototype .= '<option value="">Select a column</option>';
            foreach ($object->getFields() as $field) {
                $prototype .= '<option data-type="' .$field->getType(). '" value="' .$field->getName(). '">' .$field->getName(). '</option>';
            }
        $prototype .= '</select></td><td></td>';

        return $prototype;
    }
}

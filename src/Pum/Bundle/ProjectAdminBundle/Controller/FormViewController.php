<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\FormView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class FormViewController extends Controller
{
    const DEFAULT_NAME = 'Default';

    /**
     * @Route(path="/{_project}/{beamName}/{name}/{id}/formview/create", name="pa_formview_create")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function createAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $form = $this->createForm('pa_formview', $objectDefinition->createFormView());

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);
            $this->addSuccess('FormView successfully created');

            return $this->redirect($this->generateUrl('pa_object_edit', array(
                'beamName' => $beam->getName(),
                'name'     => $name,
                'id'       => $id,
                'view'     => $form->getData()->getName(),
            )));
        }

        return $this->render('PumProjectAdminBundle:FormView:create.html.twig', array(
            'beam'   => $beam,
            'object_definition' => $objectDefinition,
            'form'   => $form->createView(),
            'object' => $object
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/formview/{id}/{viewName}/edit", name="pa_formview_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition, $viewName)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $formView = $objectDefinition->getFormView($viewName);
        $form = $this->createForm('pa_formview', $formView);

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);
            $this->addSuccess('FormView "'.$formView->getName().'" successfully updated');

            return $this->redirect($this->generateUrl('pa_formview_edit', array(
                'beamName' => $beam->getName(),
                'name'     => $name,
                'id'       => $id,
                'viewName' => $formView->getName()
            )));
        }

        return $this->render('PumProjectAdminBundle:FormView:edit.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'form_view'         => $formView,
            'form'              => $form->createView(),
            'object'            => $object
        ));
    }

    /**
     * @Route(path="/{_project}/{beamName}/{name}/{id}/formview/{viewName}/delete", name="pa_formview_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function deleteAction(Beam $beam, $name, $id, ObjectDefinition $objectDefinition, $viewName)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        
        $objectDefinition->removeFormView($objectDefinition->getFormView($viewName));
        $this->get('pum')->saveBeam($beam);
        $this->addSuccess('FormView successfully deleted');

        return $this->redirect($this->generateUrl('pa_object_edit', array(
            'beamName' => $beam->getName(),
            'name'     => $name,
            'id'       => $id,
        )));
    }
}

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
     * @Route(path="/{_project}/formview/{beamName}/{name}/{id}/create", name="pa_formview_create")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function createAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $object = null;
        if ($id) {
            $oem = $this->get('pum.context')->getProjectOEM();
            $repository = $oem->getRepository($name);
            $this->throwNotFoundUnless($object = $repository->find($id));
        }
        $isAjax   = $request->isXmlHttpRequest();

        $form = $this->createForm('pa_formview', $objectDefinition->createFormView(), array(
            'attr' => array(
                'class'            => $isAjax ? 'yaah-js' : null,
                'data-ya-trigger'  => $isAjax ? 'submit' : null,
                'data-ya-location' => $isAjax ? 'inner' : null,
                'data-ya-target'   => $isAjax ? '#pumAjaxModal .modal-content' : null,
                'data-parent'      => $isAjax ? $request->query->get('parent_id', null) : null
            ),
            'action' => $this->generateUrl('pa_formview_create', array_merge($request->query->all(), array(
                'beamName'  => $beam->getName(),
                'name'      => $objectDefinition->getName(),
                'id'        => $id
            )))
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);
            $this->execute('php ../app/console pum:view:update');
            $this->addSuccess('FormView successfully created');

            return $this->redirect($this->generateUrl('pa_formview_edit', array(
                'beamName' => $beam->getName(),
                'name'     => $objectDefinition->getName(),
                'id'       => $id,
                'viewName' => $form->getData()->getName()
            )));
        }

        return $this->render('PumProjectAdminBundle:FormView:create.html.twig', array(
            'beam' => $beam,
            'object_definition' => $objectDefinition,
            'form' => $form->createView(),
            'object' => $object
        ));
    }

    /**
     * @Route(path="/{_project}/formview/{beamName}/{name}/{id}/{viewName}/edit/{type}", name="pa_formview_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition, $viewName, $type = 'full')
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $object = null;
        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        if ($id) {
            $this->throwNotFoundUnless($object = $repository->find($id));
        }
        $isAjax   = $request->isXmlHttpRequest();

        $formView = $objectDefinition->getFormView($viewName);
        $form = $this->createForm('pa_formview', $formView, array(
            'form_type' => $type,
            'attr' => array(
                'class'            => $isAjax ? 'yaah-js' : null,
                'data-ya-trigger'  => $isAjax ? 'submit' : null,
                'data-ya-location' => $isAjax ? 'inner' : null,
                'data-ya-target'   => $isAjax ? '#pumAjaxModal .modal-content' : null,
                'data-parent'      => $isAjax ? $request->query->get('parent_id', null) : null
            ),
            'action' => $this->generateUrl('pa_formview_edit', array_merge($request->query->all(), array(
                'beamName'  => $beam->getName(),
                'name'      => $objectDefinition->getName(),
                'id'        => $id,
                'viewName' => $formView->getName()
            )))
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);

            if (null !== $view = $formView->getView()) {
                $em = $this->getDoctrine()->getEntityManager();

                $formView->setView(null);
                $em->persist($formView);
                $em->remove($view);
                $em->flush();
            }

            $this->execute('php ../app/console pum:view:update');
            $this->addSuccess('FormView "'.$formView->getName().'" successfully updated');

            return $this->redirect($this->generateUrl('pa_formview_edit', array(
                'beamName' => $beam->getName(),
                'name' => $name,
                'id' => $id,
                'viewName' => $formView->getName()
            )));
        }

        return $this->render('PumProjectAdminBundle:FormView:edit.html.twig', array(
            'beam' => $beam,
            'object_definition' => $objectDefinition,
            'form_view' => $formView,
            'form' => $form->createView(),
            'object' => $object
        ));
    }

    /**
     * @Route(path="/{_project}/formview/{beamName}/{name}/{id}/{viewName}/delete", name="pa_formview_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function deleteAction(Beam $beam, $name, $id, ObjectDefinition $objectDefinition, $viewName)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        if ($id) {
            $oem = $this->get('pum.context')->getProjectOEM();
            $repository = $oem->getRepository($name);
            $this->throwNotFoundUnless($object = $repository->find($id));
        }

        $formView = $objectDefinition->getFormView($viewName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($formView);
        $em->flush();

        $this->addSuccess('FormView successfully deleted');

        if ($id) {
            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $id)));
        } else {
            return $this->redirect($this->generateUrl('pa_object_create', array('beamName' => $beam->getName(), 'name' => $name)));
        }
    }
}

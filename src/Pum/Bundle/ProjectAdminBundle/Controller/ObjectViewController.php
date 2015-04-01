<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\ObjectView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class ObjectViewController extends Controller
{
    const DEFAULT_NAME = 'Default';

    /**
     * @Route(path="/{_project}/objectview/{beamName}/{name}/{id}/create", name="pa_objectview_create")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function createAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        $isAjax = $request->isXmlHttpRequest();

        $form = $this->createForm('pa_objectview', $objectDefinition->createObjectView(), array(
            'attr' => array(
                'class'            => $isAjax ? 'yaah-js' : null,
                'data-ya-trigger'  => $isAjax ? 'submit' : null,
                'data-ya-location' => $isAjax ? 'inner' : null,
                'data-ya-target'   => $isAjax ? '#pumAjaxModal .modal-content' : null
            ),
            'action' => $this->generateUrl('pa_objectview_create', array_merge($request->query->all(), array(
                'beamName'  => $beam->getName(),
                'name'      => $objectDefinition->getName(),
                'id'        => $id
            )))
        ));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);
            $this->execute('php ../app/console pum:view:update');
            $this->addSuccess('ObjectView successfully created');

            return $this->redirect($this->generateUrl('pa_objectview_edit', array(
                'beamName' => $beam->getName(),
                'name'     => $objectDefinition->getName(),
                'id'       => $id,
                'viewName' => $form->getData()->getName()
            )));
        }

        return $this->render('PumProjectAdminBundle:ObjectView:create.html.twig', array(
            'beam'   => $beam,
            'object_definition' => $objectDefinition,
            'form'   => $form->createView(),
            'object' => $object
        ));
    }

    /**
     * @Route(path="/{_project}/objectview/{beamName}/{name}/{id}/{viewName}/edit/{type}", name="pa_objectview_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition, $viewName, $type = 'full')
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $objectView = $objectDefinition->getObjectView($viewName);
        $form = $this->createForm('pa_objectview', $objectView, array('form_type' => $type));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->get('pum')->saveBeam($beam);

            if (null !== $view = $objectView->getView()) {
                $em = $this->getDoctrine()->getEntityManager();

                $objectView->setView(null);
                $em->persist($objectView);
                $em->remove($view);
                $em->flush();
            }

            $this->execute('php ../app/console pum:view:update');
            $this->addSuccess('ObjectView "'.$objectView->getName().'" successfully updated');

            return $this->redirect($this->generateUrl('pa_objectview_edit', array(
                'beamName' => $beam->getName(),
                'name'     => $name,
                'id'       => $id,
                'viewName' => $objectView->getName()
            )));
        }

        return $this->render('PumProjectAdminBundle:ObjectView:edit.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'object_view'       => $objectView,
            'form'              => $form->createView(),
            'object'            => $object
        ));
    }

    /**
     * @Route(path="/{_project}/objectview/{beamName}/{name}/{id}/{viewName}/delete", name="pa_objectview_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function deleteAction(Beam $beam, $name, $id, ObjectDefinition $objectDefinition, $viewName)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $objectView = $objectDefinition->getObjectView($viewName);
        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($objectView);
        $em->flush();

        $this->addSuccess('ObjectView successfully deleted');

        return $this->redirect($this->generateUrl('pa_object_view', array(
            'beamName' => $beam->getName(),
            'name'     => $name,
            'id'       => $id,
        )));
    }
}

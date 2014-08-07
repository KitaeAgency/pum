<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomView;
use Symfony\Component\Form\FormError;

class CustomViewController extends Controller
{
    /**
     * @Route(path="/{_project}/customview", name="pa_custom_view_index")
     */
    public function customViewsListAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        return $this->render('PumProjectAdminBundle:CustomView:index.html.twig', array(
            'project' => $this->get('pum.context')->getProject(),
            'user'    => $this->getUser()
        ));
    }

    /**
     * @Route(path="/{_project}/customview/create", name="pa_custom_view_create")
     */
    public function customViewsCreateAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        $customView = new CustomView();
        $customView
            ->setProject($project = $this->get('pum.context')->getProject())
            ->setUser($this->getUser())
        ;

        if ($beamName = $request->query->get('beam')) {
            $objectFactory = $this->get('pum_core.object_factory');

            $this->throwNotFoundUnless($beam = $objectFactory->getBeam($beamName));
            $customView->setBeam($beam);

            if ($objectName = $request->query->get('object')) {
                $this->throwNotFoundUnless($object = $objectFactory->getDefinition($this->get('pum.context')->getProject()->getName(), $objectName));
                $customView->setObject($object);
            }
        }

        $repository = $this->getCustomViewRepository();
        $form       = $this->createForm('pa_custom_view', $customView);

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            if (0 != $count = $repository->existedCustomViewForUser($this->getUser(), $customView->getProject(), $customView->getBeam(), $customView->getObject())) {
                $form->addError(new FormError($this->get('translator')->trans('customview.already.existed', array(), 'pum')));

                return $this->render('PumProjectAdminBundle:CustomView:create.html.twig', array(
                    'form' => $form->createView()
                ));
            }

            $repository->save($customView);

            $this->addSuccess($this->get('translator')->trans('customview.created', array(), 'pum'));

            return $this->redirect($this->generateUrl('pa_custom_view_index'));
        }

        return $this->render('PumProjectAdminBundle:CustomView:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/{_project}/customview/delete/{id}", name="pa_custom_view_delete")
     */
    public function deleteAction($id)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        $repository = $this->getCustomViewRepository();

        $this->throwNotFoundUnless($customView = $repository->find($id));

        $repository->delete($customView);
        $this->addSuccess($this->get('translator')->trans('customview.deleted', array(), 'pum'));

        return $this->redirect($this->generateUrl('pa_custom_view_index'));
    }

    /**
     * Verifies permissions and return customview repository (or null if disabled).
     *
     * @return PermissionRepository
     */
    private function getCustomViewRepository()
    {
        if (!$this->container->has('pum.customview_repository')) {
            return null;
        }

        return $this->get('pum.customview_repository');
    }
}

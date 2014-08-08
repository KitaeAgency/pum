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
     * @Route(path="/{_project}/globalcustomview", name="pa_admin_custom_view_index")
     */
    public function adminCustomViewsListAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_DEFAULT_VIEWS');

        return $this->render('PumProjectAdminBundle:CustomView:admin_index.html.twig', array(
            'project' => $this->get('pum.context')->getProject(),
            'user'    => $this->getUser(),
            'tab'     => $request->query->get('tab')
        ));
    }

    /**
     * @Route(path="/{_project}/globalcustomview/create", name="pa_admin_custom_view_create")
     */
    public function adminCustomViewsCreateAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_DEFAULT_VIEWS');

        $project = $this->get('pum.context')->getProject();

        if ($beamName = $request->query->get('beam')) {
            $objectFactory = $this->get('pum_core.object_factory');

            $this->throwNotFoundUnless($beam = $objectFactory->getBeam($beamName));

            if ($objectName = $request->query->get('object')) {
                $this->throwNotFoundUnless($object = $beam->getObject($objectName));

                if ($tableviewName = $request->query->get('tableview')) {
                    $this->throwNotFoundUnless($tableview = $object->getTableView($tableviewName));

                    $object->setDefaultTableView($tableview);

                    $objectFactory->saveBeam($beam);

                    return $this->redirect($this->generateUrl('pa_admin_custom_view_index', array('tab' => strtolower($beam->getName()))));
                }
            }
        }

        $form = $this->createForm('pa_custom_view', $customView);

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $this->addSuccess($this->get('translator')->trans('customview.created', array(), 'pum'));

            return $this->redirect($this->generateUrl('pa_admin_custom_view_index', array('tab' => strtolower($object->getBeam()->getName()))));
        }

        return $this->render('PumProjectAdminBundle:CustomView:admin_create.html.twig', array(
            'form' => $form->createView(),
            'project' => $this->get('pum.context')->getProject()
        ));
    }

    /**
     * @Route(path="/{_project}/globalcustomview/delete", name="pa_admin_custom_view_delete")
     */
    public function adminDeleteAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_DEFAULT_VIEWS');

        if ($beamName = $request->query->get('beam')) {
            $objectFactory = $this->get('pum_core.object_factory');

            $this->throwNotFoundUnless($beam = $objectFactory->getBeam($beamName));

            if ($objectName = $request->query->get('object')) {
                $this->throwNotFoundUnless($object = $beam->getObject($objectName));

                $object->setDefaultTableView(null);

                $objectFactory->saveBeam($beam);

                $this->addSuccess($this->get('translator')->trans('admin.customview.deleted', array(), 'pum'));

                return $this->redirect($this->generateUrl('pa_admin_custom_view_index', array('tab' => $beamName)));
            }
        }

        return $this->redirect($this->generateUrl('pa_admin_custom_view_index'));
    }

    /**
     * @Route(path="/{_project}/customview", name="pa_custom_view_index")
     */
    public function customViewsListAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        return $this->render('PumProjectAdminBundle:CustomView:index.html.twig', array(
            'project' => $this->get('pum.context')->getProject(),
            'user'    => $this->getUser(),
            'tab'     => $request->query->get('tab')
        ));
    }

    /**
     * @Route(path="/{_project}/customview/create", name="pa_custom_view_create")
     */
    public function customViewsCreateAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        $repository = $this->getCustomViewRepository();
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
                $this->throwNotFoundUnless($object = $beam->getObject($objectName));
                $customView->setObject($object);

                if ($tableviewName = $request->query->get('tableview')) {
                    $this->throwNotFoundUnless($tableview = $object->getTableView($tableviewName));
                    $customView->setTableView($tableview);
                }
            }
        }

        if (null !== $customView->getTableView()) {
            if (null !== $existedCustomView = $repository->getCustomViewForUser($this->getUser(), $customView->getProject(), $customView->getBeam(), $customView->getObject())) {
                $existedCustomView->setTableView($customView->getTableView());

                $repository->save($existedCustomView);
            } else {
                $repository->save($customView);
            }

            return $this->redirect($this->generateUrl('pa_custom_view_index', array('tab' => strtolower($customView->getBeam()->getName()))));
        } else {
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

                return $this->redirect($this->generateUrl('pa_custom_view_index', array('tab' => strtolower($customView->getBeam()->getName()))));
            }

            return $this->render('PumProjectAdminBundle:CustomView:create.html.twig', array(
                'form' => $form->createView(),
                'project' => $this->get('pum.context')->getProject()
            ));
        }
    }

    /**
     * @Route(path="/{_project}/customview/delete/{id}", name="pa_custom_view_delete")
     */
    public function deleteAction($id)
    {
        $this->assertGranted('ROLE_PA_CUSTOM_VIEWS');

        $repository = $this->getCustomViewRepository();

        $this->throwNotFoundUnless($customView = $repository->find($id));
        $beamName = strtolower($customView->getBeam()->getName());

        if ($this->getUser() !== $customView->getUser()) {
            $this->throwAccessDenied('You are not allowed to delete this customview');
        }

        $repository->delete($customView);
        $this->addSuccess($this->get('translator')->trans('customview.deleted', array(), 'pum'));

        return $this->redirect($this->generateUrl('pa_custom_view_index', array('tab' => $beamName)));
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

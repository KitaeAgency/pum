<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\AppBundle\Entity\PermissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class PermissionController extends Controller
{
    /**
     * @Route(path="/permissions", name="ww_permission_list")
     */
    public function listAction(Request $request)
    {
        if (!$repository = $this->getPermissionRepository()) {
            return $this->render('PumWoodworkBundle:Permission:disabled.html.twig');
        }

        return $this->render('PumWoodworkBundle:Permission:list.html.twig', array(
                'pager' => $repository->getPage($request->query->get('page', 1))
            ));
    }

    /**
     * @Route(path="/permissions/{id}/edit", name="ww_permission_edit")
     */
    public function editAction(Request $request, $id)
    {
        /*if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));
        $form = $this->createForm('pum_group', $group);
        $groupView = clone $group;

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($group);
            $this->addSuccess(sprintf('Group "%s" successfully updated.', $group->getName()));

            return $this->redirect($this->generateUrl('ww_group_list'));
        }

        return $this->render('PumWoodworkBundle:Group:edit.html.twig', array(
                'form' => $form->createView(),
                'group' => $groupView,
                'pager' => $repository->getPage()
            ));*/
    }

    /**
     * @Route(path="/permissions/create", name="ww_permission_create")
     */
    public function createAction(Request $request)
    {
        if (!$repository = $this->getPermissionRepository()) {
            return $this->render('PumWoodworkBundle:Permission:disabled.html.twig');
        }

        $form = $this->createForm('pum_permission');

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($permission = $form->getData());
            $this->addSuccess(sprintf('Permission for "%s" successfully created.', $permission->getUser()));

            return $this->redirect($this->generateUrl('ww_permission_list'));
        }

        return $this->render('PumWoodworkBundle:Permission:create.html.twig', array(
                'form' => $form->createView(),
                'pager' => $repository->getPage()
            ));
    }

    /**
     * @Route(path="/permissions/{id}/delete", name="ww_permission_delete")
     */
    public function deleteAction($id)
    {
        /*if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:Group:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));

        $repository->delete($group);
        $this->addSuccess(sprintf('Group "%s" successfully deleted.', $group->getName()));

        return $this->redirect($this->generateUrl('ww_group_list'));*/
    }

    /**
     * Verifies permissions and return group repository (or null if disabled).
     *
     * @return PermissionRepository
     */
    private function getPermissionRepository()
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$this->container->has('pum.permission_repository')) {
            return null;
        }

        return $this->get('pum.permission_repository');
    }
}

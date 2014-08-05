<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class GroupController extends Controller
{
    /**
     * @Route(path="/groups", name="ww_group_list")
     */
    public function listAction(Request $request)
    {
        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:Group:disabled.html.twig');
        }

        return $this->render('PumWoodworkBundle:Group:list.html.twig', array(
            'pager' => $repository->getPage($request->query->get('page', 1))
        ));
    }

    /**
     * @Route(path="/groups/{id}/edit", name="ww_group_edit")
     */
    public function editAction(Request $request, $id)
    {
        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));
        $form = $this->createForm('pum_group', $group);
        $groupView = clone $group;

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($group);
            $this->addSuccess(sprintf('Group "%s" successfully updated.', $group->getName()));

            $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                $this->getUser(),
                null,
                'pum',
                $this->getUser()->getRoles()
            );

            $this->container->get('security.context')->setToken($token);

            return $this->redirect($this->generateUrl('ww_group_list'));
        }

        return $this->render('PumWoodworkBundle:Group:edit.html.twig', array(
            'form' => $form->createView(),
            'group' => $groupView,
            'pager' => $repository->getPage()
        ));
    }

    /**
     * @Route(path="/groups/create", name="ww_group_create")
     */
    public function createAction(Request $request)
    {
        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:Group:disabled.html.twig');
        }

        $form = $this->createForm('pum_group');

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($group = $form->getData());
            $this->addSuccess(sprintf('Group "%s" successfully created.', $group->getName()));

            return $this->redirect($this->generateUrl('ww_group_list'));
        }

        return $this->render('PumWoodworkBundle:Group:create.html.twig', array(
            'form' => $form->createView(),
            'pager' => $repository->getPage()
        ));
    }

    /**
     * @Route(path="/groups/{id}/delete", name="ww_group_delete")
     */
    public function deleteAction($id)
    {
        if (!$repository = $this->getGroupRepository()) {
            return $this->render('PumWoodworkBundle:Group:disabled.html.twig');
        }

        $this->throwNotFoundUnless($group = $repository->find($id));

        $repository->delete($group);
        $this->addSuccess(sprintf('Group "%s" successfully deleted.', $group->getName()));

        return $this->redirect($this->generateUrl('ww_group_list'));
    }

    /**
     * Verifies permissions and return group repository (or null if disabled).
     *
     * @return GroupRepository
     */
    private function getGroupRepository()
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$this->container->has('pum.group_repository')) {
            return null;
        }

        return $this->get('pum.group_repository');
    }
}

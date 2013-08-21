<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route(path="/users", name="ww_user_list")
     */
    public function listAction(Request $request)
    {
        if (!$repository = $this->getUserRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        return $this->render('PumWoodworkBundle:User:list.html.twig', array(
            'pager' => $repository->getPage($request->query->get('page', 1))
        ));
    }

    /**
     * @Route(path="/users/{id}/edit", name="ww_user_edit")
     */
    public function editAction(Request $request, $id)
    {
        if (!$repository = $this->getUserRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($user = $repository->find($id));
        $form = $this->createForm('pum_user', $user, array('password_required' => false));
        $userView = clone $user;

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($user);
            $this->addSuccess(sprintf('User "%s" updated.', $user->getFullname()));

            return $this->redirect($this->generateUrl('ww_user_list'));
        }

        return $this->render('PumWoodworkBundle:User:edit.html.twig', array(
            'form' => $form->createView(),
            'user' => $userView,
            'pager' => $repository->getPage()
        ));
    }

    /**
     * @Route(path="/users/create", name="ww_user_create")
     */
    public function createAction(Request $request)
    {
        if (!$repository = $this->getUserRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $form = $this->createForm('pum_user');

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $repository->save($user = $form->getData());
            $this->addSuccess(sprintf('User "%s" created.', $user->getFullname()));

            return $this->redirect($this->generateUrl('ww_user_list'));
        }

        return $this->render('PumWoodworkBundle:User:create.html.twig', array(
            'form' => $form->createView(),
            'pager' => $repository->getPage()
        ));
    }

    /**
     * @Route(path="/users/{id}/delete", name="ww_user_delete")
     */
    public function deleteAction($id)
    {
        if (!$repository = $this->getUserRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($user = $repository->find($id));

        $repository->delete($user);
        $this->addSuccess(sprintf('User "%s" deleted.', $user->getFullname()));

        return $this->redirect($this->generateUrl('ww_user_list'));
    }

    /**
     * Verifies permissions and return user repository (or null if disabled).
     *
     * @return UserRepository
     */
    private function getUserRepository()
    {
        $this->assertGranted('ROLE_WW_USERS');

        if (!$this->container->has('pum.user_repository')) {
            return null;
        }

        return $this->get('pum.user_repository');
    }
}

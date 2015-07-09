<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Pum\Bundle\AppBundle\Entity\User;

class UserController extends Controller
{
    /**
     * @Route(path="/users/{id}/edit", name="ww_user_edit")
     */
    public function editAction(Request $request, $id)
    {
        if (!$repository = $this->getUserRepository()) {
            return $this->render('PumWoodworkBundle:User:disabled.html.twig');
        }

        $this->throwNotFoundUnless($user = $repository->find($id));

        $form     = $this->createForm('pum_user', $user, array('password_required' => false,));
        $userView = clone $user;

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $repository->save($user);
            $this->addSuccess(sprintf($this->get('translator')->trans('ww.users.usergroups.user_update', array(), 'pum'), $user->getFullname()));

            return $this->redirect($this->generateUrl('ww_usergroup_list'));
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

        $form = $this->createForm('pum_user', new User, array('password_required' => false));

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $user = $form->getData();
            if (null === $user->getPassword()) {
                $pwd = User::createPwd();
                $user->setPassword($pwd, $this->get('security.encoder_factory'));
            } else {
                $pwd = $form->get('password')->getData();
            }

            $mailer = $this->get('pum.mailer');
            $mailer
                ->subject($this->get('translator')->trans('pum.users.register.subject', array(), 'pum'))
                ->from('no-reply@kitae.fr')
                ->to($user->getUsername())
                ->template('PumCoreBundle:User:Mail/register.html.twig', array(
                    'user' => $user,
                    'pwd'  => $pwd
                ))
            ;
            if ($result = $mailer->send()) {
                $this->addSuccess(sprintf($this->get('translator')->trans('ww.users.usergroups.user_success_email', array(), 'pum'), $user->getUsername()));
            } else {
                $this->addSuccess(sprintf($this->get('translator')->trans('ww.users.usergroups.user_error_email', array(), 'pum'), $user->getUsername()));
            }

            $repository->save($user);
            $this->addSuccess(sprintf($this->get('translator')->trans('ww.users.usergroups.user_create', array(), 'pum'), $user->getFullname()));

            return $this->redirect($this->generateUrl('ww_usergroup_list'));
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

        if ($user === $this->getUser()) {
            if (!is_dir($imagePath)) {
                throw new \RuntimeException(sprintf('You cannot delete yourself'));
            }
        } elseif ($user->isAdmin()) {
            throw new \RuntimeException(sprintf('You cannot delete super admin user'));
        }

        $repository->delete($user);
        $this->addSuccess(sprintf($this->get('translator')->trans('ww.users.usergroups.user_delete', array(), 'pum'), $user->getFullname()));

        return $this->redirect($this->generateUrl('ww_usergroup_list'));
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

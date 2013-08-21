<?php

namespace Pum\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends Controller
{
    /**
     * @Route(path="/login", name="app_security_login")
     */
    public function loginAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamed('', 'app_security_login');

        $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        if ($error) {
            $form->addError(new FormError('Form is invalid'));
            $request->getSession()->remove(SecurityContext::AUTHENTICATION_ERROR);
        }


        return $this->render('PumAppBundle:Security:login.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/login-check", name="app_security_loginCheck")
     */
    public function loginCheckAction()
    {
        throw new \RuntimeException('This action should not be reached');
    }

    /**
     * @Route(path="/logout", name="app_security_logout")
     */
    public function logoutAction()
    {
        throw new \RuntimeException('This action should not be reached');
    }
}

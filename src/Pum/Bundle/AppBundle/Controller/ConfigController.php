<?php

namespace Pum\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends Controller
{
    /**
     * @Route(path="/woodwork/config", name="app_config")
     */
    public function listAction(Request $request)
    {
        $this->assertGranted('ROLE_APP_CONFIG');

        return $this->render('PumAppBundle:Settings:index.html.twig', array(
            'config' => $this->get('pum.config')->all()
        ));
    }

    /**
     * @Route(path="/woodwork/config/edit", name="app_config_edit")
     */
    public function editAction(Request $request)
    {
        $this->assertGranted('ROLE_APP_CONFIG');

        $form = $this->createForm('pum_config');
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            return $this->redirect($this->generateUrl('app_config'));
        }

        return $this->render('PumAppBundle:Settings:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/woodwork/config/clearcache", name="app_config_clearcache")
     */
    public function clearCacheAction(Request $request)
    {
        $this->assertGranted('ROLE_APP_CONFIG');
        
        $config = $this->get('pum.config');

        if ($config->clear()) {
            $this->addSuccess('Config cache cleared');
        } else {
            $this->addWarning('The config cache has not been deleted');
        }

        return $this->render('PumAppBundle:Settings:index.html.twig', array(
            'config' => $config->all()
        ));
    }
}

<?php

namespace Pum\Bundle\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends Controller
{
    /**
     * @Route(path="/config", name="app_config")
     */
    public function listAction(Request $request)
    {
        return $this->render('PumAppBundle:Settings:index.html.twig', array(
            
        ));
    }

    /**
     * @Route(path="/config/edit", name="app_config_edit")
     */
    public function editAction(Request $request)
    {
        $form = $this->createForm('pum_config');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {

        }

        return $this->render('PumAppBundle:Settings:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

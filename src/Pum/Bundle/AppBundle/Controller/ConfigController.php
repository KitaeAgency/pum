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
        $config = $this->get('pum.config');

        return $this->render('PumAppBundle:Settings:index.html.twig', array(
            'config'      => $config->all(),
            'is_uptodate' => $config->isUpToDate()
        ));
    }

    /**
     * @Route(path="/config/edit", name="app_config_edit")
     */
    public function editAction(Request $request)
    {
        $form = $this->createForm('pum_config');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            return $this->redirect($this->generateUrl('app_config'));
        }

        return $this->render('PumAppBundle:Settings:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/config/clearcache", name="app_config_clearcache")
     */
    public function clearCacheAction(Request $request)
    {
        $config = $this->get('pum.config');

        if ($config->clear()) {
            $this->addSuccess('Config cache cleared');
        } else {
            $this->addWarning('The config cache has not been deleted');
        }

        return $this->render('PumAppBundle:Settings:index.html.twig', array(
            'config' => $config->all(),
            'is_uptodate' => true
        ));
    }
}

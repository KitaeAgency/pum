<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends Controller
{
    public function fieldTypeOptionsAction(Request $request)
    {
        $type = $request->query->get('type');
        $type = $this->get('pum')->getType($type)->getFormOptionsType();

        $form = $this->get('form.factory')->createNamed('__name__', $type, null, array('csrf_protection' => false))->createView();

        return $this->render('PumWoodworkBundle:Ajax:fieldTypeOptions.html.twig', array(
            'form' => $form
        ));
    }
}

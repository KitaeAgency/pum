<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends Controller
{
    /**
     * @Route(path="/admin/ajax/field-type", name="ww_ajax_field_type_options")
     */
    public function fieldTypeOptionsAction(Request $request)
    {
        $type = $request->query->get('type');
        $type = $this->get('pum')->getType($type)->getFormOptionsType();

        $form = $this->get('form.factory')->createNamed('__field_type_name__', $type, null, array('csrf_protection' => false))->createView();

        return $this->render('PumWoodworkBundle:Ajax:fieldTypeOptions.html.twig', array(
            'form' => $form
        ));
    }
}

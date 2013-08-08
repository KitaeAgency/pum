<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @Route(path="/admin/ajax/field-type", name="ww_ajax_field_type_options")
     */
    public function fieldTypeOptionsAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $type = $request->query->get('type');

        if (!$type) {
            return new Response('');
        }
        $type = $this->get('pum')->getType($type)->getFormOptionsType();

        $form = $this->get('form.factory')->createNamed('__field_type_name__', $type, null, array('csrf_protection' => false))->createView();

        return $this->render('PumWoodworkBundle:Ajax:fieldTypeOptions.html.twig', array(
            'form' => $form
        ));
    }
}

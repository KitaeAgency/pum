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
        $form = $this->get('form.factory')->createNamed('__field_type_name__', 'pum_type_options', null, array('csrf_protection' => false, 'pum_type' => $type));

        return $this->render('PumWoodworkBundle:Ajax:fieldTypeOptions.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

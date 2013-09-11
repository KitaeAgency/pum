<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
    /**
     * @Route(path="/admin/ajax/filter-type", name="ww_ajax_filter_type")
     */
    public function filterTypeAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_VIEW_EDIT');

        $type = $request->query->get('type');

        if (!$type) {
            return new Response('');
        }

        return $this->render('PumProjectAdminBundle:Ajax:filterType.html.twig', array(
            'type' => $this->get('pum')->getType($type)
        ));
    }
}

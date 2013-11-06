<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Seo\SeoSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SeoController extends Controller
{
    /**
     * @Route(path="/seo", name="ww_seo_schema_edit")
     */
    public function listAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $seoSchema = new SeoSchema($this->get('pum'), $this->get('pum.context'));
        $form = $this->createForm('ww_seo_schema', $seoSchema);

        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
            $this->addSuccess('Seo schema successfully updated');

            return $this->redirect($this->generateUrl('ww_seo_schema_edit'));
        }

        return $this->render('PumWoodworkBundle:Seo:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

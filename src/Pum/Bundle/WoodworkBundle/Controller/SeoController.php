<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Project;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class SeoController extends Controller
{
    /**
     * @Route(path="/seo", name="ww_seo_list")
     */
    public function listAction()
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        return $this->render('PumWoodworkBundle:Seo:list.html.twig', array(

        ));
    }
}

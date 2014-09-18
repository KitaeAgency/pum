<?php

namespace Pum\Bundle\CoreBundle\Controller;

use Pum\Core\Extension\Routing\RoutableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SeoController extends Controller
{
    /**
     * @Route(name="pum_object", path="/{_project}/{seo}", requirements={"seo"=".*"})
     */
    public function renderAction($seo, Request $request)
    {
        list($template, $vars) = $this->get('pum.routing')->getParameters($seoKey, $request);

        return $this->render($template, $vars);
    }

}

<?php

namespace Pum\Bundle\CoreBundle\Controller;

use Pum\Core\Extension\Routing\RoutableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SeoController extends Controller
{
    /**
     * @Route(name="pum_object", path="/{_project}/{key}", requirements={"key"=".*"})
     */
    public function renderAction($key, Request $request)
    {
        // Vars for template
        $vars = $this->get('routing_parameter')->getParameters($key);

        // Get template
        $templateName = $this->get('pum.routing')->getTemplate($vars);

        return $this->render($templateName, $vars);
    }

}

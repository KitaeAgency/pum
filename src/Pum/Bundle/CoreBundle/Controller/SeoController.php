<?php

namespace Pum\Bundle\CoreBundle\Controller;

use Pum\Core\Extension\Routing\RoutableInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SeoController extends Controller
{
    /**
     * @Route(name="pum_object", path="/pum_seo/{_project}/{seo}", requirements={"seo" = ".+"})
     */
    public function renderAction($seo)
    {
        list($template, $vars, $errors) = $this->get('pum.routing')->handleSeo($seo);

        if (!empty($errors)) {
            $message = reset($errors)['message'];

            throw $this->createNotFoundException($message);
        }

        return $this->render($template, $vars);
    }

}

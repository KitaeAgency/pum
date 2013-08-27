<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProjectController extends Controller
{
    /**
     * @Route(path="/{_project}", name="pa_homepage")
     */
    public function homepageAction()
    {
        $this->assertGranted('ROLE_PA_LIST');
        
        return $this->render('PumProjectAdminBundle:Project:homepage.html.twig');
    }
}

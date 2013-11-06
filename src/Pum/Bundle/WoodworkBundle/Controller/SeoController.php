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
     * @Route(path="/projects/{projectName}/seo", name="ww_seo_list")
     * @ParamConverter("project", class="Project")
     */
    public function listAction(Project $project)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        return $this->render('PumWoodworkBundle:Seo:list.html.twig', array(
            'project' => $project
        ));
    }
}

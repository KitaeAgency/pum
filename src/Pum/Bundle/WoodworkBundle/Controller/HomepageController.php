<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class HomepageController extends Controller
{
    /**
     * @Route(path="/", name="ww_homepage")
     */
    public function homepageAction()
    {
        return $this->render('PumWoodworkBundle:Homepage:homepage.html.twig', array(
            'projects' => $this->get('pum')->getAllProjects(),
            'beams' => $this->get('pum')->getAllBeams(),
            'userCount' => $this->get('pum.user_repository')->count()
        ));
    }
}

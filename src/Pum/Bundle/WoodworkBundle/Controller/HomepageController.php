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
        $seoCount = 0;
        foreach ($beams = $this->get('pum')->getAllBeams() as $beam) {
            $seoCount += $beam->getObjectsBy(array('seoEnabled' => true))->count();
        }

        return $this->render('PumWoodworkBundle:Homepage:homepage.html.twig', array(
            'projects' => $this->get('pum')->getAllProjects(),
            'beams' => $beams,
            'userCount' => $this->get('pum.user_repository')->count(),
            'seoCount' => $seoCount
        ));
    }
}

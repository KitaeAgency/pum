<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pum\Bundle\AppBundle\Entity\Group;

class HomepageController extends Controller
{
    /**
     * @Route(path="/", name="ww_homepage")
     */
    public function homepageAction()
    {
        $projects = $this->get('pum')->getAllProjects();

        if (false === $this->getUser()->hasWoodworkAccess() && count($projects) === 1) {
            foreach ($projects as $project) {
                return $this->redirect($this->generateUrl('pa_homepage', array('_project' => $project->getName())));
            }
        }

        $seoCount = 0;
        foreach ($beams = $this->get('pum')->getAllBeams() as $beam) {
            $seoCount += $beam->getObjectsBy(array('seoEnabled' => true))->count();
        }

        return $this->render('PumWoodworkBundle:Homepage:homepage.html.twig', array(
            'projects' => $projects,
            'beams' => $beams,
            'userCount' => $this->get('pum.user_repository')->count(),
            'seoCount' => $seoCount
        ));
    }
}

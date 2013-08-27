<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BeamController extends Controller
{
    /**
     * @Route(path="/{_project}/{beamName}", name="pa_beam_show")
     * @ParamConverter("beam", class="Beam")
     */
    public function homepageAction(Beam $beam)
    {
        $this->assertGranted('ROLE_PA_LIST');
        
        return $this->render('PumProjectAdminBundle:Beam:show.html.twig', array(
            'beam' => $beam
        ));
    }
}

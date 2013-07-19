<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Exception\BeamNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ObjectDefinitionController extends Controller
{
    public function listAction($beamName)
    {
        try {
            $beam = $this->get('pum')->getBeam($beamName);
        } catch (BeamNotFoundException $e)
        {
            throw $this->createNotFoundException(sprintf('Beam "%s" not found.', $beamName), $e);
        }

        $definitions = $this->get('pum')->getBeamDefinitions($beam);

        return $this->render('PumWoodworkBundle:ObjectDefinition:list.html.twig', array(
            'definitions' => $definitions
        ));
    }

    public function createAction()
    {
        return $this->render('PumWoodworkBundle:ObjectDefinition:create.html.twig', array(
        ));

    }
}

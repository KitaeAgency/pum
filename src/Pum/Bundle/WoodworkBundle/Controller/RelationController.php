<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RelationController extends Controller
{
    public function deleteAction($beamName, $relationId)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);
        $relation = $beam->getRelation($relationId);

        $beam->removeRelation($relation);
        $manager->saveBeam($beam);

        return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beamName)));
    }
}

<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RelationController extends Controller
{
    /**
     * @Route(path="/todo/{beamName}/{relationId}", name="ww_relation_delete")
     */
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

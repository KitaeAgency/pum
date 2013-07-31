<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Relation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RelationController extends Controller
{
    /**
     * @Route(path="/todo/{beamName}/{relationId}", name="ww_relation_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("relation", class="Relation")
     */
    public function deleteAction(Beam $beam, Relation $relation)
    {
        $manager = $this->get('pum');

        $beam->removeRelation($relation);
        $manager->saveBeam($beam);

        return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
    }
}

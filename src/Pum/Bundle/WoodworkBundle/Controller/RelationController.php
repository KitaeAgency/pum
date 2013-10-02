<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Relation\RelationSchema;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormError;

class RelationController extends Controller
{
    /**
     * @Route(path="/beams/{beamName}/schema/edit", name="ww_beam_relation_schema_edit")
     * @ParamConverter("beam", class="Beam")
     */
    public function editAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager  = $this->get('pum');
        $beamView = clone $beam;

        $relationSchema = new RelationSchema($beam, $manager);
        $form = $this->createForm('ww_relation_schema', $relationSchema);

        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
            $manager->saveBeam($beam);
            $this->addSuccess('Relations schema successfully updated');

            return $this->redirect($this->generateUrl('ww_beam_relation_schema_edit', array('beamName' => $beam->getName())));
        }

        return $this->render('PumWoodworkBundle:Relation:edit.html.twig', array(
            'beam'    => $beamView,
            'form'    => $form->createView()
        ));
    }

}

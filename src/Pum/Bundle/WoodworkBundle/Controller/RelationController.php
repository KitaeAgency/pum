<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Relation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class RelationController extends Controller
{
    /**
     * @Route(path="/beams/{beamName}/relation/create", name="ww_relation_create")
     * @ParamConverter("beam", class="Beam")
     */
    public function createAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');
        $objects = array();
        foreach ($manager->getAllBeams() as $_beam) {
            foreach ($_beam->getObjects() as $object) {
                $objects[] = $object->getName();
            }
        }

        $form = $this->createForm('ww_relation');
        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()){
            $beam->addRelation($form->getData());
            $manager->saveBeam($beam);
            $this->addSuccess('Relation successfully created');

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
        }

        return $this->render('PumWoodworkBundle:Relation:create.html.twig', array(
            'objects'  => $objects,
            'form'     => $form->createView(),
            'beam'     => $beam
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/relation/{relationId}/edit", name="ww_relation_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("relation", class="Relation")
     */
    public function editAction(Request $request, Beam $beam, Relation $relation)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');
        $objects = array();
        foreach ($manager->getAllBeams() as $_beam) {
            foreach ($_beam->getObjects() as $object) {
                $objects[] = $object->getName();
            }
        }

        $form = $this->createForm('ww_relation', $relation);
        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()){
            $manager->saveBeam($beam);
            $this->addSuccess('Relation successfully updated');

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
        }

        return $this->render('PumWoodworkBundle:Relation:edit.html.twig', array(
            'objects'  => $objects,
            'form'     => $form->createView(),
            'beam'     => $beam
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/relation/{relationId}/delete", name="ww_relation_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("relation", class="Relation")
     */
    public function deleteAction(Beam $beam, Relation $relation)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $beam->removeRelation($relation);
        $manager->saveBeam($beam);
        $this->addSuccess('Relation successfully deleted');

        return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
    }
}

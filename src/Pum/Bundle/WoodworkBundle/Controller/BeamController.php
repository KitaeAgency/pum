<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Exception\BeamNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;


class BeamController extends Controller
{
    /**
     * @Route(path="/beams", name="ww_beam_list")
     */
    public function listAction()
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        return $this->render('PumWoodworkBundle:Beam:list.html.twig', array(
            'beams' => $this->get('pum')->getAllBeams()
        ));
    }

    /**
     * @Route(path="/beams/create", name="ww_beam_create")
     */
    public function createAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $form = $this->createForm('ww_beam');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $manager->saveBeam($form->getData());

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $form->getData()->getName())));
        }

        return $this->render('PumWoodworkBundle:Beam:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/edit", name="ww_beam_edit")
     * @ParamConverter("beam", class="Beam")
     */
    public function editAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager  = $this->get('pum');
        $beamView = clone $beam;

        $form = $this->createForm('ww_beam', $beam);
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $manager->saveBeam($form->getData());

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $form->getData()->getName())));
        }

        return $this->render('PumWoodworkBundle:Beam:edit.html.twig', array(
            'beam' => $beamView,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/clone", name="ww_beam_clone")
     * @ParamConverter("beam", class="Beam")
     */
    public function cloneAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');
		
        $manager  = $this->get('pum');
        $beamView = clone $beam;

        $form = $this->createForm('ww_beam');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {

            $clone = Beam::create($form->getData()->getName());

            foreach ($beam->getObjects() as $object) {
                $clone->addObject(clone $object);
            }
            foreach ($beam->getRelations() as $relation) {
                $clone->addRelation(clone $relation);
            }

            $manager->saveBeam($clone);

            return $this->redirect($this->generateUrl('ww_beam_list'));
        }

        return $this->render('PumWoodworkBundle:Beam:clone.html.twig', array(
            'beam' => $beamView,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/delete", name="ww_beam_delete")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteAction(Beam $beam)
    {
        $manager = $this->get('pum');

        if (!$beam->isDeletable()) {
            throw $this->createNotFoundException('Beam is not deletable');
        }

        $manager->deleteBeam($beam);

        return $this->redirect($this->generateUrl('ww_beam_list'));
    }
}

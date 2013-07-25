<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Exception\BeamNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pum\Bundle\WoodworkBundle\Form\Type\ObjectDefinitionType;
use Symfony\Component\HttpFoundation\Request;
use Pum\Core\Definition\Beam;

class BeamController extends Controller
{
    public function listAction()
    {
        return $this->render('PumWoodworkBundle:Beam:list.html.twig', array(
            'beams' => $this->get('pum')->getAllBeams()
        ));
    }

    public function createAction(Request $request)
    {
        $manager = $this->get('pum');

        $form = $this->createForm('ww_beam_definition');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
    		$manager->saveBeam($form->getData());

            return $this->redirect($this->generateUrl('ww_beam_list'));
        }

        return $this->render('PumWoodworkBundle:Beam:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function editAction(Request $request, $beamName)
    {
    	$manager = $this->get('pum');
    	$beam = $manager->getBeam($beamName);

        $form = $this->createForm('ww_beam_definition', $beam);
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
    		$manager->saveBeam($form->getData());

            return $this->redirect($this->generateUrl('ww_beam_list'));
        }

        return $this->render('PumWoodworkBundle:Beam:edit.html.twig', array(
        	'beam' => $beam,
            'form' => $form->createView()
        ));
    }

}


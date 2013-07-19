<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Exception\BeamNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pum\Bundle\WoodworkBundle\Form\Type\ObjectDefinitionType;
use Symfony\Component\HttpFoundation\Request;

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

        $beam = $this->get('pum')->getBeam($beamName);
        $definitions = $beam->getObjects();

        return $this->render('PumWoodworkBundle:ObjectDefinition:list.html.twig', array(
            'beam'        => $beam,
            'definitions' => $definitions
        ));
    }

    public function createAction(Request $request, $beamName)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);
        $status = 'create';

        $form = $this->createForm('ww_object_definition');
        if ($request->getMethod() == 'POST') {
            $status = 'error';
            $form->handleRequest($request);
            if ($form->isValid()) {
                $beam->addObject($form->getData());
                $manager->saveBeam($beam);
                $status = 'success';
            }
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:create.html.twig', array(
            'beam' => $beam,
            'form' => $form->createView(),
            'status' => $status
        ));
    }

    public function viewAction($beamName, $name)
    {
        $beam = $this->get('pum')->getBeam($beamName);
        $fields = $beam->getDefinition($name)->getFields();

        return $this->render('PumWoodworkBundle:ObjectDefinition:view.html.twig', array(
            'beam'   => $beam,
            'objectName' => $name,
            'fields' => $fields
        ));
    }

    public function editAction($beamName, $name)
    {
        $beam = $this->get('pum')->getBeam($beamName);

        return $this->render('PumWoodworkBundle:ObjectDefinition:edit.html.twig', array(
            'beam'   => $beam,
            'objectName' => $name
        ));
    }

    public function deleteAction($beamName, $name)
    {
        $beam = $this->get('pum')->getBeam($beamName);

        return $this->render('PumWoodworkBundle:ObjectDefinition:delete.html.twig', array(
            'beam'   => $beam,
            'objectName' => $name
        ));
    }
}

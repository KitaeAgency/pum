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

        return $this->render('PumWoodworkBundle:ObjectDefinition:list.html.twig', array(
            'beam'        => $beam,
        ));
    }

    public function createAction(Request $request, $beamName)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);

        $form = $this->createForm('ww_object_definition');
        if ($request->getMethod() == 'POST') {
            if ($form->bind($request)->isValid()) {
                $beam->addObject($form->getData());
                $manager->saveBeam($beam);

                return $this->redirect($this->generateUrl('ww_object_definition_list', array('beamName' => $beamName)));
            }
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:create.html.twig', array(
            'beam' => $beam,
            'form' => $form->createView()
        ));
    }

    public function viewAction($beamName, $name)
    {
        $beam = $this->get('pum')->getBeam($beamName);
        $definitions = $beam->getDefinition($name);

        return $this->render('PumWoodworkBundle:ObjectDefinition:view.html.twig', array(
            'beam'   => $beam,
            'objectName' => $name,
            'definitions' => $definitions
        ));
    }

    public function editAction(Request $request, $beamName, $name)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);
        $object = $beam->getDefinition($name);
        $form = $this->createForm('ww_object_definition', $object);

        $originalFields = array();
        foreach ($object->getFields() as $field) $originalFields[] = $field;

        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()){
            $manager->saveBeam($beam);
            return $this->redirect($this->generateUrl('ww_object_definition_list', array('beamName' => $beamName)));
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:edit.html.twig', array(
            'form' => $form->createView(),
            'beam'   => $beam,
            'objectName' => $name
        ));
    }

    public function deleteAction($beamName, $name)
    {
        $beam = $this->get('pum')->getBeam($beamName);

        return $this->redirect($this->generateUrl('ww_object_definition_list', array('beamName' => $beamName)));
    }
}

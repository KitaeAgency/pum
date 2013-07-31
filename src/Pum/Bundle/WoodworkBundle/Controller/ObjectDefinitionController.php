<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\WoodworkBundle\Form\Type\ObjectDefinitionType;
use Pum\Core\Exception\BeamNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ObjectDefinitionController extends Controller
{
    /**
     * @Route(path="/objects/{beamName}/create", name="ww_object_definition_create")
     */
    public function createAction(Request $request, $beamName)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);

        $form = $this->createForm('ww_object_definition');
        if ($request->getMethod() == 'POST') {
            if ($form->bind($request)->isValid()) {
                $beam->addObject($form->getData());
                $manager->saveBeam($beam);

                return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beamName)));
            }
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:create.html.twig', array(
            'beam' => $beam,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/objects/{beamName}/{name}/edit", name="ww_object_definition_edit")
     */
    public function editAction(Request $request, $beamName, $name)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);
        $object = $beam->getObject($name);
        $form = $this->createForm('ww_object_definition', $object);

        $originalFields = array();
        foreach ($object->getFields() as $field) $originalFields[] = $field;

        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()){
            $manager->saveBeam($beam);

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beamName)));
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:edit.html.twig', array(
            'form'   => $form->createView(),
            'beam'   => $beam,
            'object' => $object
        ));
    }

    /**
     * @Route(path="/objects/{beamName}/{name}/delete", name="ww_object_definition_delete")
     */
    public function deleteAction($beamName, $name)
    {
        $manager = $this->get('pum');
        $beam = $manager->getBeam($beamName);
        $object = $beam->getObject($name);

        $beam->removeObject($object);
        $manager->saveBeam($beam);

        return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beamName)));
    }
}

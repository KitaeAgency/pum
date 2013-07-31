<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Bundle\WoodworkBundle\Form\Type\ObjectDefinitionType;
use Pum\Core\Exception\BeamNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ObjectDefinitionController extends Controller
{
    /**
     * @Route(path="/objects/{beamName}/create", name="ww_object_definition_create")
     * @ParamConverter("beam", class="Beam")
     */
    public function createAction(Request $request, Beam $beam)
    {
        $manager = $this->get('pum');

        $form = $this->createForm('ww_object_definition');
        if ($request->getMethod() == 'POST') {
            if ($form->bind($request)->isValid()) {
                $beam->addObject($form->getData());
                $manager->saveBeam($beam);

                return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
            }
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:create.html.twig', array(
            'beam' => $beam,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/objects/{beamName}/{name}/edit", name="ww_object_definition_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        $manager    = $this->get('pum');
        $objectView = clone $object;

        $form = $this->createForm('ww_object_definition', $object);
        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()){
            $manager->saveBeam($beam);

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:edit.html.twig', array(
            'form'   => $form->createView(),
            'beam'   => $beam,
            'object' => $objectView
        ));
    }

    /**
     * @Route(path="/objects/{beamName}/{name}/delete", name="ww_object_definition_delete")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function deleteAction(Beam $beam, ObjectDefinition $object)
    {
        $manager = $this->get('pum');

        $beam->removeObject($object);
        $manager->saveBeam($beam);

        return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
    }
}

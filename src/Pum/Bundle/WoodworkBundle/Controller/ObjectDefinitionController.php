<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Bundle\WoodworkBundle\Form\Type\ObjectDefinitionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Form\FormError;

class ObjectDefinitionController extends Controller
{
    /**
     * @Route(path="/objects/{beamName}/create", name="ww_object_definition_create")
     * @ParamConverter("beam", class="Beam")
     */
    public function createAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $form = $this->createForm('ww_object_definition');
        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
            $beam->addObject($form->getData());
            $manager->saveBeam($beam);
            $this->addSuccess('Object definitions successfully created');

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName(), 'pum_tab' => 'objects')));
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
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager    = $this->get('pum');
        $objectView = clone $object;

        $form = $this->createForm('ww_object_definition', $object);
        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()){
            $manager->saveBeam($beam);
            $this->addSuccess('Object definitions successfully updated');

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName(), 'type' => 'objects')));
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
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $beam->removeObject($object);
        $manager->saveBeam($beam);
        $this->addSuccess('Object definition successfully deleted');

        return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $beam->getName())));
    }


   /**
     * @Route(path="/objects/{beamName}/{name}/export", name="ww_object_definition_export")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function exportAction(Beam $beam, ObjectDefinition $object)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $d = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $beam->getName().'_'.$object->getName().'.json');
        $response->headers->set('Content-Disposition', $d);
        $response->setContent(json_encode($object->toArray()));

        return $response;
    }

    /**
     * @Route(path="/objects/{beamName}/import", name="ww_object_definition_import")
     * @ParamConverter("beam", class="Beam")
     */
    public function importAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $form = $this->createForm('ww_object_definition_import', new ObjectDefinition());
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            if (!$arrayedBeam = json_decode(file_get_contents($form->get('file')->getData()->getPathName()), true)) {
                $form->addError(new FormError('File is invalid json'));
            } else {
                try {
                    $object = ObjectDefinition::createFromArray($arrayedBeam)->setName($form->get('name')->getData());
                    $beam->addObject($object);

                    $manager->saveBeam($beam);
                    $this->addSuccess('Object definition successfully imported');

                    return $this->redirect($this->generateUrl('ww_object_definition_edit', array('beamName' => $beam->getName(), 'name' => $object->getName())));
                } catch (\InvalidArgumentException $e) {
                    $form->addError(new FormError(sprintf('Json content is invalid : %s', $e->getMessage())));
                }
            }
        }

        return $this->render('PumWoodworkBundle:ObjectDefinition:import.html.twig', array(
            'beam' => $beam,
            'form' => $form->createView()
        ));
    }
}

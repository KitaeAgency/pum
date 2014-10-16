<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
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
    public function beamRelationEditAction(Request $request, Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        if (false === $this->container->getParameter('pum_woodwork.relation_in_beam')) {
            $this->throwAccessDenied();
        }

        $objectFactory = $this->get('pum');
        $beamView      = clone $beam;

        $relationSchema = new RelationSchema($objectFactory, $beam, $object = null);
        $form = $this->createForm('ww_relation_schema', $relationSchema);

        if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
            $relationSchema = $form->getData();
            $relationSchema->flush();

            $this->addSuccess('Relations schema successfully updated');

            return $this->redirect($this->generateUrl('ww_beam_relation_schema_edit', array('beamName' => $beam->getName())));
        }

        return $this->render('PumWoodworkBundle:Relation:beam.edit.html.twig', array(
            'beam'    => $beamView,
            'form'    => $form->createView(),
            'sidebar' => array(
                'beams'   => $this->get('pum')->getAllBeams(),
                'objects' => $beamView->getObjectsOrderBy('name')
            )
        ));
    }

    /**
     * @Route(path="/objects/{beamName}/{name}/schema/edit", name="ww_object_relation_schema_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        if (true === $this->container->getParameter('pum_woodwork.relation_in_beam')) {
            $this->throwAccessDenied();
        }

        $objectFactory = $this->get('pum');
        $beamView      = clone $beam;

        $relationSchema = new RelationSchema($objectFactory, $beam, $object);
        $form = $this->createForm('ww_relation_schema', $relationSchema);

        if ($request->getMethod() == 'POST' && $form->handleRequest($request)->isValid()) {
            $relationSchema = $form->getData();
            $relationSchema->flush();

            $this->addSuccess('Relations schema successfully updated');

            return $this->redirect($this->generateUrl('ww_object_relation_schema_edit', array('beamName' => $beam->getName(), 'name' => $object->getName())));
        }

        return $this->render('PumWoodworkBundle:Relation:object.edit.html.twig', array(
            'beam'    => $beamView,
            'object'  => $object,
            'form'    => $form->createView(),
            'sidebar' => array(
                'beams'   => $this->get('pum')->getAllBeams(),
                'objects' => $beam->getObjectsOrderBy('name')
            )
        ));
    }

}

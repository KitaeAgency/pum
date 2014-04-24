<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Form\FormError;


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
            $this->addSuccess('Beam successfully created');

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $form->getData()->getName())));
        }

        return $this->render('PumWoodworkBundle:Beam:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/edit/{type}", name="ww_beam_edit")
     * @ParamConverter("beam", class="Beam")
     */
    public function editAction(Request $request, Beam $beam, $type = 'objects')
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager  = $this->get('pum');
        $beamView = clone $beam;

        $form = $this->createForm('ww_beam', $beam);
        if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
            $manager->saveBeam($form->getData());
            $this->addSuccess('Beam successfully updated');

            return $this->redirect($this->generateUrl('ww_beam_edit', array('beamName' => $form->getData()->getName(), 'type' => 'metas')));
        }

        return $this->render('PumWoodworkBundle:Beam:edit.html.twig', array(
            'pum_tab' => $type,
            'beam'    => $beamView,
            'form'    => $form->createView(),
            'sidebar' => array(
                'beams'   => $this->get('pum')->getAllBeams(),
                'objects' => $beamView->getObjects()
            )
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
        $newBeam = $beam->duplicate(); // new instance, loose binding to any existing entity.

        $form = $this->createForm('ww_beam', $newBeam);
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $manager->saveBeam($newBeam);
            $this->addSuccess('Beam successfully cloned');

            return $this->redirect($this->generateUrl('ww_beam_list'));
        }

        return $this->render('PumWoodworkBundle:Beam:clone.html.twig', array(
            'beam' => $beam,
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/beams/{beamName}/delete", name="ww_beam_delete")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteAction(Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        if (!$beam->isDeletable()) {
            throw $this->createNotFoundException('Beam is not deletable');
        }

        $manager->deleteBeam($beam);
        $this->addSuccess('Beam successfully deleted');

        return $this->redirect($this->generateUrl('ww_beam_list'));
    }

    /**
     * @Route(path="/beams/{beamName}/export", name="ww_beam_export")
     * @ParamConverter("beam", class="Beam")
     */
    public function exportAction(Beam $beam)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $zip = new \ZipArchive();
        $filename = sys_get_temp_dir() .'/'.$beam->getName().".zip";

        if ($zip->open($filename, \ZipArchive::CREATE)!== true) {
            throw new IOException('Could not write zip archive into tmp folder');
        }

        $beams = array(
            'main' => $beam->getName(),
            'related' => array()
        );

        $zip->addFromString(
            $beam->getName().".json",
            json_encode($beam->toArray(), JSON_PRETTY_PRINT)
        );

        $zip->addFromString(
            "manifest.xml",
            $this->renderView('PumWoodworkBundle:Beam:manifest.xml.twig', array('beams' => $beams))
        );

        $zip->close();
        $response = new Response(readfile($filename));
        $response->headers->set('Content-Type', 'application/zip');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $beam->getName().'.zip'
        );
        $response->headers->set('Content-Disposition', $disposition);
        return $response;
    }

    /**
     * @Route(path="/beams/import", name="ww_beam_import")
     */
    public function importAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');

        $form = $this->createForm('ww_beam_import');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            if (!$arrayedBeam = json_decode(file_get_contents($form->get('file')->getData()->getPathName()), true)) {
                $form->addError(new FormError('File is invalid json'));
            } else {
                try {
                    $beam = Beam::createFromArray($arrayedBeam)
                        ->setName($form->get('name')->getData())
                    ;

                    $manager->saveBeam($beam);

                    $this->addSuccess('Beam successfully imported');

                    return $this->redirect($this->generateUrl('ww_beam_list'));
                } catch (\InvalidArgumentException $e) {
                    $form->addError(new FormError(sprintf('Json content is invalid : %s', $e->getMessage())));
                }
            }
        }

        return $this->render('PumWoodworkBundle:Beam:import.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

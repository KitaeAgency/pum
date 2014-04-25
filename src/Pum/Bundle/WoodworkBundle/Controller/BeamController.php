<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Definition\Archive\ZipArchive;
use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

        $manager = $this->get('pum');
        $beams = array();

        foreach ($manager->getAllBeams() as $beam) {
            $beamArray= array('beamObject' => $beam );
            if ($beam->hasExternalRelations($manager->getSchema())) {
                $beamArray['hasExternalRelations'] = true;
            } else {
                $beamArray['hasExternalRelations'] = false;
            }
            $beams[] = $beamArray;
        }
        return $this->render('PumWoodworkBundle:Beam:list.html.twig', array(
            'beams' => $beams
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
    public function exportAction(Beam $beam, Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $exportExternals = $request->query->get('choice');
        $manager = $this->get('pum');

        $exportedBeam = ZipArchive::createFromBeam($beam, $manager, $exportExternals);

        $response = new BinaryFileResponse($exportedBeam->getPath());
        $response->headers->set('Content-Type', 'application/zip');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $beam->getName().'.zip'
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @param $beamZipId
     * @param $name
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(path="/beams/doimport/{beamZipId}/{name}", name="ww_beam_doimport")
     */
    public function doImportAction($beamZipId, $name)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $manager = $this->get('pum');


        $archive = $this->get('woodwork.zip.storage')->getZip($beamZipId);
        $files = $archive->getBeamListFromZip($beamZipId);
        $manifest = $archive->getManifest();

        foreach ($files as $jsonBeamName) {
            if (!$arrayedBeam = json_decode($archive->getFileByName($jsonBeamName), true)) {
                $this->addError('File is invalid json');
            } elseif ($manager->hasBeam($arrayedBeam['name']) && $manifest['main'] != $arrayedBeam['name']) {
                $this->addError('A beam named: '.$arrayedBeam['name'].' already exist');
            } else {
                try {
                    $beam = Beam::createFromArray($arrayedBeam);

                    if ($manifest['main'] == $arrayedBeam['name']) {
                        $beam->setName($name);
                    }

                    $manager->saveBeam($beam);

                    $this->addSuccess(
                        $this->get('translator')->trans(
                            'ww.beams.import.success',
                            array('%name%' => $arrayedBeam['name']),
                            'pum'
                        )
                    );

                } catch (\InvalidArgumentException $e) {
                    $this->addError(sprintf('Json content is invalid : %s', $e->getMessage()));
                }
            }
        }
        return $this->redirect($this->generateUrl('ww_beam_list'));
    }

    /**
     * @Route(path="/beams/import", name="ww_beam_import")
     */
    public function importAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $form = $this->createForm('ww_beam_import');

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {

            $archive = new ZipArchive($form->get('file')->getData()->getPathName());
            $files = $archive->getBeamListFromZip();

            $formData = array(
                'name' => $form->get('name')->getData(),
                'beamZipId' => $this->get('woodwork.zip.storage')->saveZip($archive)
            );

            return $this->render('PumWoodworkBundle:Beam:import_confirm.html.twig', array(
                'files' => $files,
                'formData' => $formData,
            ));
        }

        return $this->render('PumWoodworkBundle:Beam:import.html.twig', array(
            'form' => $form->createView()
        ));
    }
}

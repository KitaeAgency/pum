<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Pum\Core\Definition\Archive\ZipArchive;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Relation\Relation;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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
        $beams =$manager->getAllBeams();

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

        //Todo fix sleeping relations
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
        $schema = $this->get('pum')->getSchema();

        $exportedBeam = ZipArchive::createFromBeam($beam, $schema, $exportExternals);

        if ($request->query->has('beam-store')) {

            $body = json_encode(array(
                "zip" => $request->getUriForPath($this->get('woodwork.zip.storage')->saveZipForWeb($exportedBeam)),
                "name" => $beam->getName(),
                "icon" => $beam->getIcon(),
                "color"=> $beam->getColor(),
                "objects" => $beam->getObjectsNamesAsArray()
            ));

            $client = new Client();
            try {
                $client->put(
                    $this->container->getParameter('beam_store_url_put'),
                    ['body' => $body]
                );
                $this->addSuccess(
                    $this->get('translator')->trans(
                        'ww.beams.import.store.beam_uploaded',
                        array('%name%' => $beam->getName()),
                        'pum'
                    )
                );
            } catch (ClientException $exception) {
                $this->addError(
                    $this->get('translator')->trans('ww.beams.import.store.store_unavailable', array(), 'pum')
                );
                return $this->redirect($this->generateUrl('ww_beam_list'));
            }

            return $this->redirect($this->generateUrl('ww_beam_store_list'));
        }

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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @Route(path="/beams/doimport/", name="ww_beam_doimport")
     */
    public function doImportAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $formData = $request->request->get('form');

        $name = $formData['name'];
        $beamZipId = $formData['beamZipId'];

        $manager = $this->get('pum');

        $archive = $this->get('woodwork.zip.storage')->getZip($beamZipId);
        $files = $archive->getBeamListFromZip($beamZipId);
        $manifest = $archive->getManifest();


        $importedBeamNames = array();

        foreach ($files as $jsonBeamName) {
            $arrayedBeam = json_decode($archive->getFileByName($jsonBeamName), true);
            if ($manifest['main'] != $arrayedBeam['name']) {
                $name = $arrayedBeam['name'];
            }
            if (isset($formData[$name]) && $formData[$name] == Relation::IMPORT_RENAME) {
                $importedBeamNames[$name] = $formData[$name.'_rename'];
            } else {
                $importedBeamNames[$name] = $name;
            }
        }

        foreach ($files as $jsonBeamName) {
            $arrayedBeam = json_decode($archive->getFileByName($jsonBeamName), true);

            $name = $importedBeamNames[$arrayedBeam['name']];
            try {
                $arrayedBeam = $this->importRelationsForBeam($arrayedBeam, $importedBeamNames);
                $beam = Beam::createFromArray($arrayedBeam);
            } catch (\InvalidArgumentException $e) {
                $this->addError(sprintf('Json content is invalid : %s', $e->getMessage()));
                continue;
            }

            if ($manager->hasBeam($name) && $formData[$name] == Relation::IMPORT_OVERWRITE) {
                $manager->deleteBeam($manager->getBeam($name));
            }

            if ($manager->hasBeam($name) && $formData[$name] == Relation::IMPORT_IGNORE) {
                continue;
            }

            $beam->setName($name);

            $manager->saveBeam($beam);

            $this->addSuccess(
                $this->get('translator')->trans(
                    'ww.beams.import.success',
                    array('%name%' => $arrayedBeam['name']),
                    'pum'
                )
            );

        }
        return $this->redirect($this->generateUrl('ww_beam_list'));
    }

    /**
     * Parse arrayed beam relations to setup relations
     *
     * @param $arrayedBeam
     * @param $importedBeamNames
     * @return mixed
     */
    private function importRelationsForBeam($arrayedBeam, $importedBeamNames)
    {
        $manager = $this->get('pum');
        foreach ($arrayedBeam['objects'] as $objectKey => $object) {
            foreach ($object['fields'] as $fieldKey => $field) {
                if ($field['type'] == FieldDefinition::RELATION_TYPE) {
                    $targetBeam = $arrayedBeam['objects'][$objectKey]['fields'][$fieldKey]['typeOptions']['target_beam'];
                    if (isset($importedBeamNames[$targetBeam])) {
                        $arrayedBeam['objects'][$objectKey]['fields'][$fieldKey]['typeOptions']['target_beam'] = $importedBeamNames[$targetBeam];
                    }
                    if ($field['typeOptions']['is_external']) {
                        try {
                            $targetBeam = $manager->getBeam($field['typeOptions']['target_beam']);
                            if ($targetBeam->getSeed() != $field['typeOptions']['target_beam_seed']) {
                                $arrayedBeam['objects'][$objectKey]['fields'][$fieldKey]['typeOptions']['is_sleeping'] = true;
                                $this->addError($this->get('translator')->trans(
                                    'ww.beams.import.relation.slept',
                                    array('%relation_name%' => $field['name'], '%name%' => $arrayedBeam['name']),
                                    'pum'
                                ));
                            }
                            if ($targetBeam->getSignature() != md5($field['typeOptions']['target_beam_seed'] . json_encode($arrayedBeam))) {
                                $this->addWarning($this->get('translator')->trans(
                                    'ww.beams.import.relation.wrong_version',
                                    array('%relation_name%' => $field['name'], '%name%' => $arrayedBeam['name']),
                                    'pum'
                                ));
                            }
                        } catch (DefinitionNotFoundException $exception) {
                            $arrayedBeam['objects'][$objectKey]['fields'][$fieldKey]['typeOptions']['is_sleeping'] = true;
                            $this->addError($this->get('translator')->trans(
                                'ww.beams.import.relation.slept',
                                array('%relation_name%' => $field['name'], '%name%' => $arrayedBeam['name']),
                                'pum'
                            ));
                        }
                    }
                }
            }
        }
        return $arrayedBeam;
    }

    /**
     * @Route(path="/beams/import", name="ww_beam_import")
     */
    public function importAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $form = $this->createForm('ww_beam_import');

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {

            $parseResult = $this->parseImportedBeam($form);

            return $this->render('PumWoodworkBundle:Beam:import_confirm.html.twig', array(
                'form' => $parseResult['summaryForm']->getForm()->createView(),
                'emptyForm' => $parseResult['emptyForm'],
                'files' => $parseResult['files'],
                'formData' => $parseResult['formData'],
                'beamDiff' => $parseResult['beamDiff']
            ));
        } elseif (strpos($form->getErrors()->__toString(), 'ZipNotFound')) {
            $this->addError(
                $this->get('translator')->trans(
                    'ww.beams.import.store.zip_not_found',
                    array('%name%' => $form->get('name')->getData()),
                    'pum'
                )
            );
            return $this->redirect($this->generateUrl('ww_beam_store_list'));
        }

        return $this->render('PumWoodworkBundle:Beam:import.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * Parse uploaded zip archive for conflicts
     *
     * @param $form
     * @return array
     */
    private function parseImportedBeam(Form $form)
    {
        $archive = new ZipArchive($form->get('file')->getData()->getPathName());

        $name = $form->get('name')->getData();

        $formData = array(
            'name' => $name,
            'beamZipId' => $this->get('woodwork.zip.storage')->saveZip($archive)
        );

        $manager = $this->get('pum');

        //Retrieve archive file list
        $files = $archive->getBeamListFromZip();

        //Retrieve archive manifest file
        $manifest = $archive->getManifest();
        $emptyForm = true;

        //Build the summary form in order to add one field for each beam in conflict
        $summaryForm = $this->createFormBuilder($formData)
            ->setAction($this->generateUrl('ww_beam_doimport'))
            ->add('name', 'hidden')
            ->add('beamZipId', 'hidden');

        $beamDiff = array();
        foreach ($files as $jsonBeamName) {
            if (!$arrayedBeam = json_decode($archive->getFileByName($jsonBeamName), true)) {
                $this->addError('File is invalid json');
                continue;
            }
            //Set up the beam name according to json file if the beam it's main imported beam leave the user imputed once
            if ($manifest['main'] != $arrayedBeam['name']) {
                $name = $arrayedBeam['name'];
            }
            //Checking out if a beam with the same name exist
            if ($manager->hasBeam($name)) {
                $beam = $manager->getBeam($name);
                if ($beam->getSeed() == $arrayedBeam['seed']
                    && $beam->getSignature() != md5($arrayedBeam['seed'] . json_encode($arrayedBeam))
                ) {
                    $beamDiff[$name] = $beam->getDiff($arrayedBeam);
                }
                $emptyForm = false;
                $summaryForm->add($name, 'choice', array(
                    'label' => $this->get('translator')->trans(
                        'ww.beams.import.summary.conflict.label',
                        array('%beam_name%' => $name),
                        'pum'
                    ),
                    'choices'   => array(
                        'rename' => $this->get('translator')->trans(
                            'ww.beams.import.summary.conflict.choice.rename',
                            array('%beam_name%' => $name),
                            'pum'
                        ),
                        'overwrite' => $this->get('translator')->trans(
                            'ww.beams.import.summary.conflict.choice.overwrite',
                            array('%beam_name%' => $name),
                            'pum'
                        ),
                        'ignore' => $this->get('translator')->trans(
                            'ww.beams.import.summary.conflict.choice.ignore',
                            array('%beam_name%' => $name),
                            'pum'
                        )
                    ),
                    'empty_value' => false,
                    'expanded' => true,
                    'required'  => false,
                    'attr' => array('wrapper_class' => 'linked-field-toggle')
                ));

                $summaryForm->add($name.'_rename', 'text', array(
                    'label' => $this->get('translator')->trans(
                        'ww.beams.import.summary.conflict.rename.label',
                        array('%beam_name%' => $name),
                        'pum'
                    ),
                    'required'  => false,
                    'attr' => array('class' => 'linked-field')
                ));
            }
        }

        $summaryForm->add(
            'save',
            'submit',
            array('label' => $this->get('translator')->trans('ww.beams.import.summary.confirm', array(), 'pum'))
        );

        return array(
            'summaryForm' => $summaryForm,
            'emptyForm' => $emptyForm,
            'files' => $files,
            'beamDiff' => $beamDiff,
            'formData' => $formData
        );
    }

    /**
     * @Route(path="/store/list.json", name="ww_beam_store_list_json")
     */
    public function mockStoreAction()
    {
        return new BinaryFileResponse($this->container->getParameter('kernel.root_dir').'/../web/store/list.json');
    }

    /**
     * @Route(path="/store", name="ww_beam_store_list")
     */
    public function listStoreAction()
    {
        $this->assertGranted('ROLE_WW_BEAMS');

        $client = new Client();
        $beamStore = $client->get($this->container->getParameter('beam_store_url'))->json();

        $beams = $beamStore['beams'];

        foreach ($beams as $key => $beam) {
            $form = $this->createForm('ww_beam_import', array('name' => $beam['name'], 'url' => $beam['zipUrl']));
            $beams[$key]['form'] = $form->createView();
        }

        return $this->render('PumWoodworkBundle:Beam:store_list.html.twig', array(
            'beams' => $beams
        ));
    }
}

<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Pum\Core\Extension\Util\Namer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Form\FormError;

class VarsController extends Controller
{
    /**
     * @Route(path="/{_project}/vars", name="pa_vars_index")
     */
    public function listAction()
    {
        $this->assertGranted('ROLE_PA_VARS');

        return $this->render('PumProjectAdminBundle:Vars:index.html.twig', array(
            'vars' => $this->get('pum.vars')->all()
        ));
    }

    /**
     * @Route(path="/{_project}/vars/export", name="pa_vars_export")
     */
    public function exportAction()
    {
        $this->assertGranted('ROLE_PA_VARS');

        $json     = json_encode($this->get('pum.vars')->all(), JSON_PRETTY_PRINT);
        $response = new Response($json, 200, array('content-type' => 'application/json'));
        $d        = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            Namer::toLowercase($this->get('pum.context')->getProject()->getName().'_vars_export').'.json'
        );

        $response->headers->set('Content-Disposition', $d);

        return $response;
    }

    /**
     * @Route(path="/{_project}/vars/import", name="pa_vars_import")
     */
    public function importAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_VARS');

        $form = $this->createForm('pum_var_export');

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if (!$dataVars = json_decode(file_get_contents($form->get('file')->getData()->getPathName()), true)) {
                $form->addError(new FormError($this->get('translator')->trans('pa.vars.invalid_json', array(), 'pum')));
            } else {
                $vars = $this->get('pum.vars');
                $aVar = array('key' => null, 'value' => null, 'type' => 'string', 'description' => null);

                $vars->refresh();

                if ($form->get('delete_old')->getData()) {
                    $vars->deleteAll();
                }

                foreach ($dataVars as $var) {
                    $data = array_merge($aVar, $var);
                    $vars->set($var['key'], $var['value'], $var['type'], $var['description']);
                }

                $vars->flush();
                $this->addSuccess($this->get('translator')->trans('pa.vars.import_success', array(), 'pum'));

                return $this->redirect($this->generateUrl('pa_vars_index'));
            }
        }

        return $this->render('PumProjectAdminBundle:Vars:import.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/{_project}/vars/create", name="pa_vars_create")
     */
    public function createAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_VARS');

        $form = $this->createForm('pum_var');
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->addSuccess($this->get('translator')->trans('pa.vars.create_success', array(), 'pum'));

            return $this->redirect($this->generateUrl('pa_vars_index'));
        }

        return $this->render('PumProjectAdminBundle:Vars:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/{_project}/vars/edit/{key}", name="pa_vars_edit")
     */
    public function editAction(Request $request, $key)
    {
        $this->assertGranted('ROLE_PA_VARS');

        $var = $this->get('pum.vars')->get($key);

        $form = $this->createForm('pum_var', $var);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $this->addSuccess($this->get('translator')->trans('pa.vars.update_success', array(), 'pum'));

            return $this->redirect($this->generateUrl('pa_vars_index'));
        }

        return $this->render('PumProjectAdminBundle:Vars:edit.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/{_project}/vars/delete/{key}", name="pa_vars_delete")
     */
    public function deleteAction($key)
    {
        $this->assertGranted('ROLE_PA_VARS');

        $vars = $this->get('pum.vars');
        $vars->remove($key);
        $vars->flush();

        $this->addSuccess($this->get('translator')->trans('pa.vars.delete_success', array(), 'pum'));

        return $this->redirect($this->generateUrl('pa_vars_index'));
    }
}

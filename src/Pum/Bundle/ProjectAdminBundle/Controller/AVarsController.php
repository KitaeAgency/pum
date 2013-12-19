<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Definition\Beam;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class AVarsController extends Controller
{
    /**
     * @Route(path="/{_project}/vars", name="pa_vars_index")
     */
    public function listAction()
    {
        $this->assertGranted('ROLE_PA_LIST');

        return $this->render('PumProjectAdminBundle:Vars:index.html.twig', array(
            'vars' => $this->get('pum.vars')->all()
        ));
    }

    /**
     * @Route(path="/{_project}/vars/create", name="pa_vars_create")
     */
    public function createAction(Request $request)
    {
        $this->assertGranted('ROLE_PA_LIST');

        $form = $this->createForm('pum_var');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $this->addSuccess('Var successfully created');

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
        $this->assertGranted('ROLE_PA_LIST');

        $var = $this->get('pum.vars')->get($key);

        $form = $this->createForm('pum_var', $var);
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $this->addSuccess('Var successfully updated');

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
        $this->assertGranted('ROLE_PA_LIST');

        $vars = $this->get('pum.vars');
        $vars->remove($key);
        $vars->flush();

        $this->addSuccess('Var '.$key.' successfully deleted');

        return $this->redirect($this->generateUrl('pa_vars_index'));
    }
}

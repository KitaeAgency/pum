<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\WoodworkBundle\Form\Type\ObjectType;
use Pum\Core\Definition\Project;
use Pum\Core\Exception\ProjectNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends Controller
{
    /**
     * Render for projects dropdown menu
     */
    public function menuAction()
    {
        $this->assertGranted('ROLE_WW_PROJECTS');

        return $this->render('PumWoodworkBundle:Project:menu.html.twig', array(
            'projects' => $this->get('pum')->getAllProjects()
        ));
    }


    /**
     * @Route(path="/projects", name="ww_project_list")
     */
    public function listAction()
    {
        $this->assertGranted('ROLE_WW_PROJECTS');

        return $this->render('PumWoodworkBundle:Project:list.html.twig', array(
            'projects' => $this->get('pum')->getAllProjects()
        ));
    }

    /**
     * @Route(path="/projects/create", name="ww_project_create")
     */
    public function createAction(Request $request)
    {
        $this->assertGranted('ROLE_WW_PROJECTS');

        $manager = $this->get('pum');

        $form = $this->createForm('ww_project');
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $manager->saveProject($form->getData());

            return $this->redirect($this->generateUrl('ww_project_list'));
        }

        return $this->render('PumWoodworkBundle:Project:create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route(path="/projects/{projectName}/edit", name="ww_project_edit")
     * @ParamConverter("project", class="Project")
     */
    public function editAction(Request $request, Project $project)
    {
        $this->assertGranted('ROLE_WW_PROJECTS');

        $manager     = $this->get('pum');
        $projectView = clone $project;

        $form = $this->createForm('ww_project', $project);
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $manager->saveProject($form->getData());

            return $this->redirect($this->generateUrl('ww_project_list'));
        }

        return $this->render('PumWoodworkBundle:Project:edit.html.twig', array(
            'project' => $projectView,
            'form'    => $form->createView()
        ));
    }

    /**
     * @Route(path="/projects/{projectName}/delete", name="ww_project_delete")
     * @ParamConverter("project", class="Project")
     */
    public function deleteAction(Project $project)
    {
        $this->assertGranted('ROLE_WW_PROJECTS');

        $manager = $this->get('pum');
        $manager->deleteProject($project);

        return $this->redirect($this->generateUrl('ww_project_list'));
    }
}

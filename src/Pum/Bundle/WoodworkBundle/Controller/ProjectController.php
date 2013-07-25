<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Core\Exception\ProjectNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Pum\Bundle\WoodworkBundle\Form\Type\ObjectType;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends Controller
{
    public function listAction()
    {
        return $this->render('PumWoodworkBundle:Project:list.html.twig', array(
            'projects' => $this->get('pum')->getAllProjects()
        ));
    }

    public function createAction(Request $request)
    {
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

    public function editAction(Request $request, $projectName)
    {
    	$manager = $this->get('pum');
    	$project = $manager->getProject($projectName);

        $form = $this->createForm('ww_project', $project);
        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
    		$manager->saveProject($form->getData());

            return $this->redirect($this->generateUrl('ww_project_list'));
        }

        return $this->render('PumWoodworkBundle:Project:edit.html.twig', array(
        	'project' => $project,
            'form' => $form->createView()
        ));
    }

    public function deleteAction($projectName)
    {
        $manager = $this->get('pum');
        $project = $manager->getProject($projectName);
        $manager->deleteProject($project);

        return $this->redirect($this->generateUrl('ww_project_list'));
    }
}
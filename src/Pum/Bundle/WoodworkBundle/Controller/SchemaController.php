<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Psr\Log\NullLogger;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SchemaController extends Controller
{
    public function indexAction()
    {
        return $this->render('PumWoodworkBundle:Schema:index.html.twig', array(
            'pum' => $this->get('pum')
        ));
    }

    public function updateAction()
    {
        foreach ($this->get('pum')->getAllProjects() as $project) {
            $this->get('pum')->getExtension(EmFactoryExtension::NAME)->updateSchema($project, new NullLogger());
        }

        return $this->redirect($this->generateUrl('ww_schema'));
    }
}

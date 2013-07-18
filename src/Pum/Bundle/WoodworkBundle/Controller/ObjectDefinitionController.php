<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ObjectDefinitionController extends Controller
{
    public function listAction()
    {
        $definitions = $this->get('pum')->getAllDefinitions();

        return $this->render('PumWoodworkBundle:ObjectDefinition:list.html.twig', array(
            'definitions' => $definitions
        ));
    }

    public function createAction()
    {
    
        return $this->render('PumWoodworkBundle:ObjectDefinition:create.html.twig', array(

        ));

    }
}
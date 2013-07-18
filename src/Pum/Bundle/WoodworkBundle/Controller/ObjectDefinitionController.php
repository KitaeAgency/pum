<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ObjectDefinitionController extends Controller
{
    public function listAction()
    {
        $em = $this->get('doctrine')->getManager();

        $definitions = $em->getRepository('Pum:ObjectDefinition')->findAll();

        return $this->render('PumWoodworkBundle:ObjectDefinition:list.html.twig', array(
            'definitions' => $definitions
        ));
    }
}

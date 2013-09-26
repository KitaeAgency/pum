<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Psr\Log\NullLogger;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SchemaController extends Controller
{
    /**
     * @Route(path="/schema", name="ww_schema")
     */
    public function indexAction()
    {
        $this->assertGranted('ROLE_WW_SCHEMA');

        return $this->render('PumWoodworkBundle:Schema:index.html.twig', array(
            'pum' => $this->get('pum')
        ));
    }

    /**
     * @Route(path="/schema/update", name="ww_schema_update")
     */
    public function updateAction()
    {
        $this->assertGranted('ROLE_WW_SCHEMA');

        foreach ($this->get('pum')->getAllProjects() as $project) {
            $this->get('pum')->getExtension(EmFactoryExtension::NAME)->updateSchema($project, new NullLogger());
        }

        return $this->redirect($this->generateUrl('ww_schema'));
    }
}

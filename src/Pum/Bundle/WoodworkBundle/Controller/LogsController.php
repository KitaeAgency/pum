<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Psr\Log\NullLogger;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class LogsController extends Controller
{
    /**
     * @Route(path="/logs", name="ww_logs")
     */
    public function indexAction()
    {
        $this->assertGranted('ROLE_WW_LOGS');

        return $this->render('PumWoodworkBundle:Logs:index.html.twig', array(
            'pum' => $this->get('pum')
        ));
    }

    /**
     * @Route(path="/logs/update", name="ww_logs_update")
     */
    public function updateAction()
    {
        $this->assertGranted('ROLE_WW_LOGS');

        foreach ($this->get('pum')->getAllProjects() as $project) {
            $this->get('pum')->saveProject($project);
        }

        return $this->redirect($this->generateUrl('ww_logs'));
    }
}

<?php

namespace Pum\Bundle\WizardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;

class InstallController extends ContainerAware
{
    /**
    * @Route("/_wizard/database", name="_wizard_database")
    * @Method("POST")
    */
    public function stepDatabaseAction()
    {
        return $this->execute('php ../app/console doctrine:database:drop --force --quiet || true; php ../app/console doctrine:database:create');
    }

    /**
    * @Route("/_wizard/schema", name="_wizard_schema")
    * @Method("POST")
    */
    public function stepSchemaAction()
    {
        return $this->execute('php ../app/console doctrine:schema:create');
    }

    /**
    * @Route("/_wizard/beams", name="_wizard_beams")
    * @Method("POST")
    */
    public function stepBeamsAction()
    {
        return $this->execute('php ../app/console pum:beam:import');
    }

    /**
    * @Route("/_wizard/fixtures", name="_wizard_fixtures")
    * @Method("POST")
    */
    public function stepFixturesAction()
    {
        return $this->execute('php ../app/console doctrine:fixtures:load --append');
    }

    /**
    * @Route("/_wizard/templates", name="_wizard_templates")
    * @Method("POST")
    */
    public function stepTemplatesAction()
    {
        return $this->execute('php ../app/console pum:templates:import');
    }

    private function execute($command)
    {
        $status = 200;
        $data = array();
        $error = null;
        $process = new Process($command);

        try {
            $process->run();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if (!$process->isSuccessful()) {
            $error = $process->getOutput();
        }

        if ($error) {
            $status = 500;
            $data['error'] = $error;
        }

        return new JsonResponse($data, $status);
    }
}

<?php

namespace Pum\QA\Context;

use Behat\Behat\Context\BehatContext;
use Pum\Core\Exception\BeamNotFoundException;
use Pum\QA\Initializer\AppAwareInterface;

class ApiContext extends BehatContext implements AppAwareInterface
{
    protected $runCallback;

    public function setRunCallback($runCallback)
    {
        $this->runCallback = $runCallback;
    }

    private function run($callback)
    {
        if (null === $this->runCallback) {
            throw new \RuntimeException('Run callback missing');
        }
        return call_user_func($this->runCallback, $callback);
    }

    /**
     * @Given /^no beam "([^"]+)" exists$/
     */
    public function noBeamExists($name)
    {
        $this->run(function ($container) use ($name) {
            $pum = $container->get('pum');
            try {
                $pum->deleteBeam($pum->getBeam($name));
            } catch (BeamNotFoundException $e) {
            }
        });
    }
}

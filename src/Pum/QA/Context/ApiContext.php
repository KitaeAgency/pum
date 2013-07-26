<?php

namespace Pum\QA\Context;

use Behat\Behat\Context\BehatContext;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Exception\BeamNotFoundException;
use Pum\Core\Exception\DefinitionNotFoundException;
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

    /**
     * @Given /^beam "([^"]+)" exists$/
     */
    public function beamExists($name)
    {
        return $this->run(function ($container) use ($name) {
            $pum = $container->get('pum');
            try {
                $beam = $pum->getBeam($name);
            } catch (BeamNotFoundException $e) {
                $beam = Beam::create($name);
                $pum->saveBeam($beam);
            }

            return $beam;
        });
    }

    /**
     * @Given /^object "([^"]*)" from beam "([^"]*)" has no field "([^"]*)"$/
     */
    public function objectFromBeamHasNoField($objectName, $beamName, $fieldName)
    {
        $this->beamExists($beamName);

        $this->run(function ($container) use ($beamName, $objectName, $fieldName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);

            try {
                $object = $beam->getObject($objectName);
            } catch (DefinitionNotFoundException $e) {
                $object = ObjectDefinition::create($objectName);
                $beam->addObject($object);
            }

            try {
                $field = $object->getField($fieldName);
                $object->removeField($field);
            } catch (DefinitionNotFoundException $e) {
            }

            $pum->saveBeam($beam);
        });
    }

    /**
     * @Then /^object "([^"]*)" from beam "([^"]*)" should have field "([^"]*)"$/
     */
    public function objectFromBeamShouldHaveField($objectName, $beamName, $fieldName)
    {
        $this->run(function ($container) use ($beamName, $objectName, $fieldName) {
            $container->get('pum')
                ->getBeam($beamName)
                ->getObject($objectName)
                ->getField($fieldName)
            ;
        });
    }
}

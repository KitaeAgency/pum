<?php

namespace Pum\QA\Context;

use Behat\Behat\Context\BehatContext;
use Behat\Gherkin\Node\TableNode;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Relation;
use Pum\Core\Exception\BeamNotFoundException;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Exception\ProjectNotFoundException;
use Pum\Core\Exception\RelationNotFoundException;
use Pum\Extension\EmFactory\EmFactoryExtension;
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
     * @Given /^table view "([^"]+)" does not exist for object "([^"]+)" in beam "([^"]+)"$/
     */
    public function tableViewDoesNotExistForObjectInProject($view, $object, $beam)
    {
        $this->run(function ($container) use ($view, $object, $beam) {
            $pum = $container->get('pum');

            $beam = $pum->getBeam($beam);
            $object = $beam->getObject($object);

            if ($object->hasTableView($view)) {
                $object->removeTableView($object->getTableView($view));
                $pum->saveBeam($beam);
            }
        });
    }

    /**
     * @Given /^form view "([^"]+)" does not exist for object "([^"]+)" in beam "([^"]+)"$/
     */
    public function formViewDoesNotExistForObjectInProject($view, $object, $beam)
    {
        $this->run(function ($container) use ($view, $object, $beam) {
            $pum = $container->get('pum');

            $beam = $pum->getBeam($beam);
            $object = $beam->getObject($object);

            if ($object->hasFormView($view)) {
                $object->removeFormView($object->getFormView($view));
                $pum->saveBeam($beam);
            }
        });
    }

    /**
     * @Given /^table view "([^"]+)" exists for object "([^"]+)" in beam "([^"]+)"$/
     */
    public function tableViewExistsForObjectInProject($view, $object, $beam)
    {
        $this->run(function ($container) use ($view, $object, $beam) {
            $pum = $container->get('pum');

            $beam = $pum->getBeam($beam);
            $object = $beam->getObject($object);

            if (!$object->hasTableView($view)) {
                $object->createTableView($view);
                $pum->saveBeam($beam);
            }
        });
    }

    /**
     * @Given /^no project "([^"]+)" exists$/
     */
    public function noProjectExists($name)
    {
        $this->run(function ($container) use ($name) {
            $pum = $container->get('pum');

            try {
                $pum->deleteProject($pum->getProject($name));
            } catch (ProjectNotFoundException $e) {
            }
        });
    }

    /**
     * @Given /^project "([^"]+)" exists$/
     */
    public function projectExists($name)
    {
        return $this->run(function ($container) use ($name) {
            $pum = $container->get('pum');

            try {
                $pum->getProject($name);

                return;
            } catch (ProjectNotFoundException $e) {
            }

            $project = Project::create($name);
            $pum->saveProject($project);

            return $project;
        });
    }

    /**
     * @Given /^project "([^"]*)" has beam "([^"]*)"$/
     */
    public function projectHasBeam($project, $beam)
    {
        $this->projectExists($project);
        $this->beamExists($beam);

        $this->run(function ($container) use ($project, $beam) {
            $pum     = $container->get('pum');
            $beam    = $pum->getBeam($beam);
            $project = $pum->getProject($project);

            if (!$project->hasBeam($beam)) {
                $project->addBeam($beam);
            }

            $pum->saveProject($project);
        });
    }

    /**
     * @Given /^project "([^"]*)" has following "([^"]*)" objects:$/
     */
    public function projectHasFollowingObjects($project, $object, TableNode $table)
    {
        $rows = $table->getRows();

        if (count($rows) < 2) {
            throw new InvalidArgumentException('Expecting a table with at least two rows');
        }

        // first line: fields
        $fields = $rows[0];

        $this->run(function ($container) use ($fields, $rows, $project, $object) {
            $oem = $container->get('pum')->getExtension(EmFactoryExtension::NAME)->getManager($project);

            // delete existing objects
            foreach ($oem->getRepository($object)->findAll() as $obj) {
                $oem->remove($obj);
            }

            // create new ones
            for ($i = 1, $count = count($rows); $i < $count; $i++) {
                $obj = $oem->createObject($object);
                for ($j = 0, $jCount = count($rows[$i]); $j < $jCount; $j++) {
                    $obj->set($fields[$j], $rows[$i][$j]);
                }


                $oem->persist($obj);
            }

            $oem->flush();
        });
    }

    /**
     * @Given /^no beam "([^"]+)" exists$/
     */
    public function noBeamExists($name)
    {
        $this->run(function ($container) use ($name) {
            $pum = $container->get('pum');
            try {
                $beam = $pum->getBeam($name);
                $pum->deleteBeam($beam);
            } catch (BeamNotFoundException $e) {
            }
        });
    }

    /**
     * @Given /^user "([^"]+)" does not exist$/
     */
    public function userDoesNotExist($username)
    {
        $this->run(function ($container) use ($username) {
            $repository = $container->get('pum.user_repository');
            $user = $repository->findOneBy(array('username' => $username));
            if ($user) {
                $repository->delete($user);
            }
        });
    }

    /**
     * @Given /^user "([^"]+)" exists$/
     */
    public function userExists($username)
    {
        $this->run(function ($container) use ($username) {
            $repository = $container->get('pum.user_repository');
            $user = $repository->findOneBy(array('username' => $username));
            if (!$user) {
                $user = new User($username);
                $user->setPassword($username, $container->get('security.encoder_factory'));
                $user->setFullname(ucfirst($username));
                $repository->save($user);
            }
        });
    }

    /**
     * @Given /^group "([^"]+)" does not exist$/
     */
    public function groupDoesNotExist($name)
    {
        $this->run(function ($container) use ($name) {
            $repository = $container->get('pum.group_repository');
            $group = $repository->findOneBy(array('name' => $name));
            if ($group) {
                $repository->delete($group);
            }
        });
    }

    /**
     * @Given /^beam "([^"]+)" exists$/
     */
    public function beamExists($name)
    {
        $this->run(function ($container) use ($name) {
            $pum = $container->get('pum');

            try {
                $pum->getBeam($name);

                return;
            } catch (BeamNotFoundException $e) {
            }

            $beam = Beam::create($name)
                ->setIcon('airplane')
                ->setColor('orange')
            ;

            $pum->saveBeam($beam);

            return $beam;
        });
    }

    /**
     * @Given /^object "([^"]*)" from beam "([^"]*)" exists$/
     */
    public function objectFromBeamExists($objectName, $beamName)
    {
        $this->beamExists($beamName);

        $this->run(function ($container) use ($beamName, $objectName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);

            try {
                $object = $beam->getObject($objectName);

                return;
            } catch (DefinitionNotFoundException $e) {
            }

            $object = ObjectDefinition::create($objectName);
            $beam->addObject($object);

            $pum->saveBeam($beam);
        });
    }

    /**
     * @Given /^object "([^"]*)" from beam "([^"]*)" does not exist$/
     */
    public function objectFromBeamDoesNotExist($objectName, $beamName)
    {
        $this->beamExists($beamName);

        $this->run(function ($container) use ($beamName, $objectName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);

            try {
                $object = $beam->getObject($objectName);
                $beam->getObjects()->removeElement($object);
                $pum->saveBeam($beam);
            } catch (DefinitionNotFoundException $e) {
            }

        });
    }

    /**
     * @Given /^object "([^"]*)" from beam "([^"]*)" has no field at all$/
     */
    public function objectFromBeamHasNoFieldAtAll($objectName, $beamName)
    {
        $this->objectFromBeamExists($objectName, $beamName);

        $this->run(function ($container) use ($beamName, $objectName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);
            $object = $beam->getObject($objectName);

            $fields = $object->getFields();

            foreach ($fields as $field) {
                $object->removeField($field);
            }

            $pum->saveBeam($beam);
        });
    }


    /**
     * @Given /^object "([^"]*)" from beam "([^"]*)" has no field "([^"]*)"$/
     */
    public function objectFromBeamHasNoField($objectName, $beamName, $fieldName)
    {
        $this->objectFromBeamExists($objectName, $beamName);

        $this->run(function ($container) use ($beamName, $objectName, $fieldName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);
            $object = $beam->getObject($objectName);

            try {
                $field = $object->getField($fieldName);
                $object->removeField($field);
                $pum->saveBeam($beam);
            } catch (DefinitionNotFoundException $e) {
            }

        });
    }

    /**
     * @Given /^object "([^"]*)" from beam "([^"]*)" has field "([^"]*)"$/
     */
    public function objectFromBeamHasField($objectName, $beamName, $fieldName)
    {
        $this->objectFromBeamHasNoField($objectName, $beamName, $fieldName);

        $this->run(function ($container) use ($beamName, $objectName, $fieldName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);
            $object = $beam->getObject($objectName);
            $object->addField(FieldDefinition::create($fieldName, 'text'));
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

    /**
     * @Given /^relation "([^"]*)" from beam "([^"]*)" exists$/
     */
    public function relationFromBeamExists($relationData, $beamName)
    {
        $this->beamExists($beamName);
        $relationData = explode(' ', $relationData);

        $this->run(function ($container) use ($relationData, $beamName) {
            $pum = $container->get('pum');
            $beam = $pum->getBeam($beamName);

            $beam->addRelation(Relation::create($relationData[0], $relationData[1], $relationData[3], $relationData[4], $relationData[2]));

            $pum->saveBeam($beam);
        });
    }
}

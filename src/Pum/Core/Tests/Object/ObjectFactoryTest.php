<?php

namespace Pum\Core\Tests\Object;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Driver\StaticDriver;
use Pum\Core\Object\ObjectFactory;
use Pum\Core\SchemaManager;
use Pum\Core\Tests\SchemaManagerTest;

class ObjectFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testSchemaUpdate()
    {
        list($factory, $manager) = $this->createFactoryAndManager();

        $entity = $factory->createObject('car');
        $entity->set('name', 'foo');

        $beam = $manager->getBeam('cars');
        $beam->getObject('car')->createField('description', 'text');
        $manager->saveBeam($beam);
        $factory->clearCache(); // usually done by extension

        $entity = $factory->createObject('car');
        $entity->set('description', 'foo');

    }

    private function createFactoryAndManager()
    {
        $manager = SchemaManagerTest::createSchemaManager();

        $car = new ObjectDefinition('car');
        $car->createField('name', 'text');

        $beam = new Beam('cars');
        $beam->addObject($car);
        $manager->saveBeam($beam);

        $project = new Project('foo');
        $project->addBeam($beam);
        $manager->saveProject($project);

        $factory = new ObjectFactory($manager, 'foo', sys_get_temp_dir().'/pum_tmp_'.md5(uniqid().microtime()));

        return array($factory, $manager);
    }
}

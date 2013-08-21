<?php

namespace Pum\Core\Tests;
use Doctrine\DBAL\DriverManager;
use Pum\Core\Config;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Driver\StaticDriver;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\SchemaManager;
use Pum\Core\Type\Factory\StaticTypeFactory;
use Pum\Core\Type\TextType;

class SchemaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExtension()
    {
        $manager = self::createSchemaManager();
        $manager->addExtension($ext = new myExtension());

        $this->assertSame($ext, $manager->getExtension('my'));
    }

    public function testEvents()
    {
        $manager = self::createSchemaManager();
        $manager->addExtension(new myExtension());

        $manager->saveBeam(Beam::create('foo'));
        $manager->saveProject(Project::create('foo'));

        $this->assertCount(2, $manager->getExtension('my')->getEvents());
    }

    public static function createSchemaManager()
    {
        $driver      = new StaticDriver();
        $typeFactory = new StaticTypeFactory(array(
            'text' => new TextType()
        ));

        $file   = tempnam(sys_get_temp_dir(), 'pum_');
        unlink($file);
        return new SchemaManager($driver, $typeFactory, $file);
    }

    public static function createConnection()
    {
        // delete file at the end of test
        $file   = tempnam(sys_get_temp_dir(), 'pum_');
        register_shutdown_function(function () use ($file) {
            unlink($file);
        });

        return DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
            'path'   => $file,
        ));

    }
}

class myExtension extends \Pum\Core\Extension\AbstractExtension
{
    protected $events = array();

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'my';
    }

    public function resetEvents()
    {
        $this->events = array();
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function onProjectChange(ProjectEvent $event)
    {
        $this->events[] = array(Events::PROJECT_CHANGE, $event);
    }

    public function onProjectDelete(ProjectEvent $event)
    {
        $this->events[] = array(Events::PROJECT_DELETE, $event);
    }

    public function onBeamChange(BeamEvent $event)
    {
        $this->events[] = array(Events::BEAM_CHANGE, $event);
    }

    public function onBeamDelete(BeamEvent $event)
    {
        $this->events[] = array(Events::BEAM_DELETE, $event);
    }
}

<?php

namespace Pum\Core\Tests;
use Doctrine\DBAL\DriverManager;
use Pum\Core\BuilderRegistry\StaticBuilderRegistry;
use Pum\Core\Config;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\EventListener\Event\BeamEvent;
use Pum\Core\EventListener\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\ObjectFactory;
use Pum\Core\Schema\StaticSchema;
use Pum\Core\Type\Factory\StaticTypeFactory;
use Pum\Extension\Core\Type\TextType;
use Pum\Extension\EmFactory\EmFactoryExtension;

class ObjectFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $manager = self::createObjectFactory();
    }

    public static function createObjectFactory()
    {
        $schema      = new StaticSchema();
        $registry    = new StaticBuilderRegistry(array(
            'text' => new TextType()
        ));

        return new ObjectFactory($registry, $schema);
    }

    public static function createConnection()
    {
        // delete file at the end of test
        $file   = tempnam(sys_get_temp_dir(), 'pum_');
        register_shutdown_function(function () use ($file) {
            // Skip windows message error on unwrittable file
            @unlink($file);
        });

        return DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
            'path'   => $file,
        ));

    }
}

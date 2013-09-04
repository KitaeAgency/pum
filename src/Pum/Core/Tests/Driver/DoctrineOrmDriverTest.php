<?php

namespace Pum\Core\Tests\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Driver\DoctrineOrmDriver;

class DoctrineOrmDriverTest extends AbstractDriverTest
{
    public function createDriver($hash)
    {
        return new DoctrineOrmDriver(self::createEntityManager($hash));
    }

    static public function createEntityManager($hash)
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not present.');
        }

        // delete file at the end of test
        $file   = sys_get_temp_dir().'/pum_'.md5($hash);
        register_shutdown_function(function () use ($file) {
            unlink($file);
        });

        $config = Setup::createConfiguration();
        $config->setMetadataDriverImpl(new SimplifiedYamlDriver(array(
            __DIR__.'/../../Resources/config/doctrine' => 'Pum\Core\Definition'
        )));

        $conn   = array(
            'driver' => 'pdo_sqlite',
            'path'   => $file,
        );

        $em = EntityManager::create($conn, $config);

        $st = new SchemaTool($em);
        $st->createSchema($em->getMetadataFactory()->getAllMetadata());

        return $em;
    }
}

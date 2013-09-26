<?php

namespace Pum\Core\Tests\Schema;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Schema\DoctrineOrmSchema;

class DoctrineOrmSchemaTest extends AbstractSchemaTest
{
    public function createSchema($hash)
    {
        return new DoctrineOrmSchema(self::createEntityManager($hash));
    }

    static public function createEntityManager($hash)
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not present.');
        }

        // delete file at the end of test
        $file   = sys_get_temp_dir().'/pum_'.md5($hash);
        register_shutdown_function(function () use ($file) {
            // Skip windows message error on unwrittable file
            @unlink($file);
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

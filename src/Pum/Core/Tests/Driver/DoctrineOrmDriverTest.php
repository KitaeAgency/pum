<?php

namespace Pum\Core\Tests\Driver;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Driver\DoctrineOrmDriver;

class DoctrineOrmDriverTest extends AbstractDriverTest
{
    public function getDriver()
    {
        return new DoctrineOrmDriver($this->getEntityManager());
    }

    private function getEntityManager()
    {
        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not present.');
        }

        // delete file at the end of test
        $file   = tempnam(sys_get_temp_dir(), 'pum_');
        register_shutdown_function(function () use ($file) {
            unlink($file);
        });

        $config = Setup::createAnnotationMetadataConfiguration(array('/var/www/pum.se/src'), true);
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

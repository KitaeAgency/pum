<?php

namespace Pum\Core\Tests\Schema;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
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

    public static function createEntityManager($hash)
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

        AnnotationRegistry::registerAutoloadNamespace(
            'Symfony\Bridge\Doctrine\Validator\Constraints',
            __DIR__.'/../../../../../vendor/symfony/symfony/src'
        );

        AnnotationRegistry::registerAutoloadNamespace(
            'Symfony\Component\Validator\Constraints',
            __DIR__.'/../../../../../vendor/symfony/symfony/src'
        );

        $coreBundleDriver = $config->newDefaultAnnotationDriver(__DIR__.'/../../../Bundle/CoreBundle/Entity', false);
        $appBundleDriver = $config->newDefaultAnnotationDriver(__DIR__.'/../../../Bundle/AppBundle/Entity', false);
        $projectAdminBundleDriver = $config->newDefaultAnnotationDriver(__DIR__.'/../../../Bundle/ProjectAdminBundle/Entity', false);

        $driverChain = new DriverChain();
        $driverChain->addDriver(new SimplifiedYamlDriver(array(
            __DIR__.'/../../Resources/config/doctrine' => 'Pum\Core\Definition',
        )), 'Pum\Core\Definition');
        $driverChain->addDriver($coreBundleDriver, 'Pum\Bundle\CoreBundle\Entity');
        $driverChain->addDriver($appBundleDriver, 'Pum\Bundle\AppBundle\Entity');
        $driverChain->addDriver($projectAdminBundleDriver, 'Pum\Bundle\ProjectAdminBundle\Entity');
        $config->setMetadataDriverImpl($driverChain);

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

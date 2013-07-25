<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Schema;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool as OrmSchemaTool;
use Psr\Log\LoggerInterface;
use Pum\Core\Definition\Project;
use Pum\Core\Extension\EmFactory\Doctrine\ObjectEntityManager;

class SchemaTool
{
    /**
     * @var Project
     */
    protected $project;

    /**
     * @var ObjectEntityManager
     */
    protected $manager;

    public function __construct(Project $project, ObjectEntityManager $manager)
    {
        $this->project = $project;
        $this->manager = $manager;
    }

    public function update(LoggerInterface $logger)
    {
        $classes = array();
        foreach ($this->project->getObjects() as $object) {
            $classes[] = $this->manager->getObjectMetadata($object->getName());
        }


        $conn = $this->manager->getConnection();

        $from = $this->getFromSchema($classes);
        $to   = $this->getToSchema($classes);
        $comparator = new Comparator();
        $diff       = $comparator->compare($from, $to);

        $orders = $diff->toSql($conn->getDatabasePlatform());

        $logger->debug(sprintf('%s SQL orders to execute to migrate project "%s".', count($orders), $this->project->getName()));
        foreach ($orders as $order) {
            $logger->info(sprintf('SQL query: %s', $order));
            $conn->executeQuery($order);
        }
    }

    private function getFromSchema(array $classes)
    {
        $schemaTables = array_map(function ($metadata) {
            return $metadata->getTableName();
        }, $classes);

        $sm = $this->manager->getConnection()->getSchemaManager();
        $tables = $sm->listTables();
        $filtered = array();

        foreach ($tables as $table) {
            if (false === array_search($table->getName(), $schemaTables)) {
                continue;
            }

            $filtered[] = $table;
        }

        // needs to filter sequences here later

        return new Schema($filtered, array(), $sm->createSchemaConfig());
    }

    private function getToSchema(array $classes)
    {
        $tool = new OrmSchemaTool($this->manager);

        return $tool->getSchemaFromMetadata($classes);
    }
}

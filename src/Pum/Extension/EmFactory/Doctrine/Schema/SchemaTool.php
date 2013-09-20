<?php

namespace Pum\Extension\EmFactory\Doctrine\Schema;

use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Tools\SchemaTool as OrmSchemaTool;
use Pum\Core\Definition\Project;
use Pum\Extension\EmFactory\Doctrine\ObjectEntityManager;

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

    public function update()
    {
        $classes = array();
        foreach ($this->project->getObjects() as $object) {
            $classes[] = $this->manager->getObjectMetadata($object->getName());
        }

        $conn = $this->manager->getConnection();

        $from = $this->getFromSchema();
        $to   = $this->getToSchema($classes);
        $comparator = new Comparator();
        $diff       = $comparator->compare($from, $to);

        $orders = $diff->toSql($conn->getDatabasePlatform());

        foreach ($orders as $order) {
            $conn->executeQuery($order);
        }
    }

    private function getFromSchema()
    {
        $tableMatch = $this->manager->getObjectFactory()->getTableNamePattern();

        $sm = $this->manager->getConnection()->getSchemaManager();
        $tables = $sm->listTables();
        $filtered = array();

        foreach ($tables as $table) {
            if (!preg_match($tableMatch, $table->getName())) {
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

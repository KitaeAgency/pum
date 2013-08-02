<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Core\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\SchemaManager;

/**
 * Context class for PUM applications.
 */
class PumContext
{
    /**
     * @var SchemaManager
     */
    private $schemaManager;

    /**
     * @var string
     */
    private $projectName;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function getProjectName()
    {
        return $this->projectName;
    }

    public function getAllProjects()
    {
        return $this->schemaManager->getAllProjects();
    }

    /**
     * @return PumContext
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;

        return $this;
    }

    /**
     * @return PumContext
     */
    public function removeProjectName()
    {
        $this->projectName = null;

        return $this;
    }

    /**
     * @return ObjectEntityManager
     *
     * @throws RuntimeException project is not set in context.
     */
    public function getProjectOEM()
    {
        if (null === $this->projectName) {
            throw new \RuntimeException(sprintf('Project name is missing from PUM context.'));
        }

        return $this->schemaManager->getExtension(EmFactoryExtension::NAME)->getManager($this->projectName);
    }
}

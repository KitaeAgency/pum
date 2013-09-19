<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Extension\EmFactory\EmFactoryExtension;
use Pum\Core\ObjectFactory;

/**
 * Context class for PUM applications.
 */
class PumContext
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var string
     */
    private $projectName;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    public function getProjectName()
    {
        return $this->projectName;
    }

    public function getProject()
    {
        if (null === $this->projectName) {
            return null;
        }

        return $this->objectFactory->getProject($this->projectName);
    }

    public function getAllProjects()
    {
        return $this->objectFactory->getAllProjects();
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

        return $this->objectFactory->getExtension(EmFactoryExtension::NAME)->getManager($this->projectName);
    }
}

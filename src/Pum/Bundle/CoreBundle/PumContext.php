<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Core\ObjectFactory;
use Pum\Core\Extension\EmFactory\EmFactory;

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
     * @var EmFactory
     */
    private $emFactory;

    /**
     * @var string
     */
    private $projectName;

    public function __construct(ObjectFactory $objectFactory, EmFactory $emFactory)
    {
        $this->objectFactory = $objectFactory;
        $this->emFactory     = $emFactory;
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

        return $this->emFactory->getManager($this->objectFactory, $this->projectName);
    }
}

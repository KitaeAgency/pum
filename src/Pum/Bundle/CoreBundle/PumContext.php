<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Bundle\CoreBundle\Routing\PumUrlGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Context class for PUM applications.
 */
class PumContext
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $projectName;

    /**
     * static cache
     *
     * @var Pum\Bundle\CoreBundle\Routing\PumUrlGenerator
     */
    private $projectRouting;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

        return $this->container->get('pum')->getProject($this->projectName);
    }

    public function getAllProjects()
    {
        return $this->container->get('pum')->getAllProjects();
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
        $this->projectRouting = null;

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

        return $this->container->get('em_factory')->getManager($this->container->get('pum'), $this->projectName);
    }

    /**
     * @return RoutingTable
     */
    public function getProjectRouting()
    {
        if (null === $this->projectName) {
            throw new \RuntimeException(sprintf('Project name is missing from PUM context.'));
        }

        if (null === $this->projectRouting) {
            $this->projectRouting = new PumUrlGenerator(
                $this->container->get('router'),
                $this->container->get('routing_factory')->getRouting($this->projectName)
            );
        }

        return $this->projectRouting;
    }
}

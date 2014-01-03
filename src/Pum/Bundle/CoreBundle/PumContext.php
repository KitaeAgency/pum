<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Bundle\CoreBundle\Routing\PumUrlGenerator;
use Pum\Core\Vars\MysqlVars;
use Pum\Core\Config\MysqlConfig;
use Pum\Core\Exception\ClassNotFoundException;
use Pum\Core\Extension\Search\SearchEngine;
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

    /**
     * static cache
     *
     * @var Pum\Core\Vars\MysqlVars
     */
    private $projectVars;

    /**
     * static cache
     *
     * @var Pum\Core\Config\MysqlConfig
     */
    private $projectConfig;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $ctx = $this;
        spl_autoload_register(function ($class) use ($ctx) {
            $ctx->loadClass($class);
        });
    }

    public function getSearchEngine()
    {
        return $this->container->get('pum.search_engine')->setProjectName($this->getProjectName());
    }

    public function search($objectName, $text)
    {
        return $this->getSearchEngine()->search($objectName, $text);
    }

    public function searchGlobal($text)
    {
        return $this->getSearchEngine()->searchGlobal($text);
    }

    public function loadClass($class)
    {
        $project = $this->getProjectName();

        if (null === $project) {
            return;
        }

        $pum = $this->container->get('pum');

        if (!$pum->isProjectClass($class)) {
            return;
        }

        try {
            $pum->loadClassFromCache($class, $project);
        } catch (ClassNotFoundException $e) {
        }
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
        $this->projectVars = null;

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
     * @return PumUrlGenerator
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

    /**
     * @return MysqlVars
     */
    public function getProjectVars()
    {
        if (null === $this->projectName) {
            throw new \RuntimeException(sprintf('Project name is missing from PUM context.'));
        }

        if (null === $this->projectVars) {
            $this->projectVars = new MysqlVars(
                $this->container->get('doctrine.dbal.default_connection'),
                $this->projectName,
                $cacheFolder = $this->container->getParameter('kernel.cache_dir').'/pum_vars'
            );
        }

        return $this->projectVars;
    }

    /**
     * @return MysqlConfig
     */
    public function getProjectConfig()
    {
        if (null === $this->projectConfig) {
            $this->projectConfig = new MysqlConfig(
                $this->container->get('doctrine.dbal.default_connection'),
                $cacheFolder = $this->container->getParameter('kernel.cache_dir').'/pum_config'
            );
        }

        return $this->projectConfig;
    }
}

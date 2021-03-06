<?php

namespace Pum\Bundle\CoreBundle;

use Pum\Bundle\CoreBundle\Routing\PumRouting;
use Pum\Core\Config\MysqlConfig;
use Pum\Core\Exception\ClassNotFoundException;
use Pum\Core\Extension\Search\SearchEngine;
use Pum\Core\Vars\MysqlVars;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Pum\Core\Extension\Media\Metadata\MediaMetadataStorage;

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
     * @var \Pum\Bundle\CoreBundle\Routing\PumUrlGenerator
     */
    private $projectRouting;

    /**
     * static cache
     *
     * @var \Pum\Core\Vars\MysqlVars
     */
    private $projectVars;

    /**
     * static cache
     *
     * @var \Pum\Core\Config\MysqlConfig
     */
    private $projectConfig;

    /**
     * MediaStorage
     * @var \Pum\Core\Extension\Media\Metadata\MediaMetadataStorage
     */
    private $projectMediaMetadataStorage;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $ctx = $this;
        spl_autoload_register(function ($class) use ($ctx) {
            $ctx->loadClass($class);
        });
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return SearchEngine
     */
    public function getSearchEngine()
    {
        return $this->container->get('pum.search_engine')->setProjectName($this->getProjectName());
    }

    /**
     * @param $objectName
     * @param $text
     * @return \Pum\Core\Extension\Search\Result\Result
     */
    public function search($objectName, $text)
    {
        return $this->getSearchEngine()->search($objectName, $text);
    }

    /**
     * @param $text
     * @return \Pum\Core\Extension\Search\Result\Result
     */
    public function searchGlobal($text)
    {
        return $this->getSearchEngine()->searchGlobal($text);
    }

    /**
     * @param $class
     */
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

    /**
     * @return string
     */
    public function getProjectName()
    {
        return $this->projectName;
    }

    /**
     * @return null|\Pum\Core\Definition\Project
     */
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
     * @param String $projectName
     * @return PumContext
     */
    public function setProjectName($projectName)
    {
        $this->projectName = $projectName;
        $this->getMediaMetadataStorage()->refreshProjectName($projectName);

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
     * @param String $entityManagerName
     * @return \Doctrine\Common\Persistence\ObjectManager
     * @throws \RuntimeException The project is not set in context.
     */
    public function getProjectOEM($entityManagerName = 'default')
    {
        if (null === $this->projectName) {
            throw new \RuntimeException(sprintf('Project name is missing from PUM context.'));
        }

        return $this->container->get('em_factory')->getManager($this->container->get('pum'), $this->projectName, $entityManagerName);
    }

    /**
     * @throws \RuntimeException
     * @return PumUrlGenerator
     */
    public function getProjectRouting()
    {
        if (null === $this->projectName) {
            throw new \RuntimeException(sprintf('Project name is missing from PUM context.'));
        }

        if (null === $this->projectRouting) {
            $this->projectRouting = new PumRouting(
                $this,
                $this->container->get('routing_seo_generator'),
                $this->container->get('routing_factory')->getRouting($this->projectName)
            );
        }

        return $this->projectRouting;
    }

    /**
     * @throws \RuntimeException
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

    /**
     * @return Pum\Core\Extension\Media\Metadata\MediaMetadataStorage
     */
    public function getMediaMetadataStorage()
    {
        if (null === $this->projectMediaMetadataStorage) {
            $this->projectMediaMetadataStorage = new MediaMetadataStorage(
                $this->container->get('doctrine.dbal.default_connection'),
                $this->projectName
            );
        }

        return $this->projectMediaMetadataStorage;
    }
}

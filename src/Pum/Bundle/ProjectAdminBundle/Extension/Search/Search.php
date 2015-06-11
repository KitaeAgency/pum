<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Util\Namer;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Doctrine\Common\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
 */
class Search implements SearchInterface
{
    const DEFAULT_LIMIT = 10;

    const RESPONSE_TEMPLATE       = 'PumProjectAdminBundle:Search:list.html.twig';
    const RESPONSE_COUNT_TEMPLATE = 'PumProjectAdminBundle:Search:count.html.twig';

    const CACHE_NAMESPACE = 'pum_schema';
    const CACHE_ID        = 'search_schema_project_';
    const CACHE_TTL       = 86400;

    public static $searchableTypes = array(
        'text',
        'integer',
    );

    /**
     * @var PumContext
     */
    protected $context;

    /**
     * @var authorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
    *
    * @var cacheProvider
    */
    private $cache;

    public function __construct(PumContext $context, AuthorizationChecker $authorizationChecker, UrlGeneratorInterface $urlGenerator, $cacheFolder)
    {
        $this->context              = $context;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator         = $urlGenerator;
        $this->setCache($cacheFolder);
    }

    public function search($q, Beam $beam = null, ObjectDefinition $objectDefinition = null)
    {
        $schema     = $this->getSchema();
        $beamName   = $beam ? $beam->getName() : null;
        $objectName = $objectDefinition ? $objectDefinition->getName() : null;

        switch (true) {
            case null === $objectDefinition: // Count Search
                $template = self::RESPONSE_COUNT_TEMPLATE;

                $params = array(
                    'beam'              => $beam,
                    'object_definition' => $objectDefinition,
                    'results'           => $this->count($q, $beamName, $objectName)
                );
                break;

            default:
                $template   = self::RESPONSE_TEMPLATE;
                $repository = $this->getRepository($objectName);

                // QB stuff
                foreach ($schema as $beam) {
                    foreach ($beam['objects'] as $object) {
                        if ($object['name'] == $objectName) {
                            $qb = $repository->getSearchResult($q, null, $object['fields'], $limit = null, $offset = null, $returnQuery = true);
                        }
                    }
                }

                if (!isset($qb)) {
                    $this->clearCache(); // Schema might be not updated
                    throw new \RuntimeException(sprintf("The pum object '%s' does not exist.", $objectName));
                }

                // Render
                $params = array(
                    'qb'    => $qb,
                    'count' => $this->count($q, $beamName, $objectName)
                );
        }

        return array($template, $params);
    }

    /**
    * {@inheritDoc}
    */
    public function clearCache()
    {
        if (null !== $this->cache) {
            $this->cache->delete($this->getCacheId());
        }

        return $this;
    }

    protected function count($q, $beamName, $objectName)
    {
        $schema = $this->getSchema();

        switch (true) {
            case null === $objectName:
                $res = array();
                foreach ($schema as $k => $beam) {
                    if (null === $beamName || $beamName == $beam['name']) {
                        $res[$k] = array(
                            'name'    => $beam['name'],
                            'label'   => $beam['label'],
                            'icon'    => $beam['icon'],
                            'color'   => $beam['color'],
                        );

                        foreach ($beam['objects'] as $object) {
                            if ($count = $this->getRepository($object['name'])->getSearchCountResult($q, null, $object['fields'])) {
                                $res[$k]['objects'][] = array(
                                    'name'  => $object['name'],
                                    'label' => $object['label'],
                                    'count' => $count,
                                    'path'  => $this->urlGenerator->generate('pa_search', array(
                                        'q'        => $q,
                                        'beamName' => $beam['name'],
                                        'name'     => $object['name'],
                                    ))
                                );
                            }
                        }

                        if (!isset($res[$k]['objects'])) {
                            unset($res[$k]);
                        }
                    }
                }

                return $res;

            default:
                foreach ($schema as $beam) {
                    foreach ($beam['objects'] as $object) {
                        if ($object['name'] == $objectName) {
                            return $this->getRepository($objectName)->getSearchCountResult($q, null, $object['fields']);
                        }
                    }
                }

                throw new \RuntimeException(sprintf("The pum object '%s' does not exist.", $objectName));
        }
    }

    protected function get($service_id)
    {
        return $this->context->getContainer()->get($service_id);
    }

    protected function getRepository($objectName)
    {
        return $this->context->getProjectOEM()->getRepository($objectName);
    }

    protected function getSchema()
    {
        $schema = $this->cache->fetch($this->getCacheId());

        if (!$schema) {
            $this->cache->save($this->getCacheId(), $schema = $this->initSchema(), $lifeTime = self::CACHE_TTL);
        }

        return $schema;
    }

    protected function initSchema()
    {
        if (null === $project = $this->context->getProject()) {
            throw new \RuntimeException(sprintf('Project name is missing from PUM context.'));
        }

        $schema = array();
        foreach ($project->getBeams() as $k => $beam) {
            $schema[$k] = array(
                'id'    => $beam->getId(),
                'name'  => $beam->getName(),
                'label' => $beam->getAliasName(),
                'icon'  => $beam->getIcon(),
                'color' => $beam->getColor()
            );
            foreach ($beam->getObjects() as $_k => $object) {
                $schema[$k]['objects'][$_k] = array(
                    'id'    => $object->getId(),
                    'name'  => $object->getName(),
                    'label' => $object->getAliasName()
                );
                foreach ($object->getFields() as $field) {
                    if (in_array($field->getType(), self::$searchableTypes)) {
                        $schema[$k]['objects'][$_k]['fields'][] = $field->getCamelCaseName();
                    }
                }

                if (empty($schema[$k]['objects'][$_k]['fields'])) {
                    unset($schema[$k]['objects'][$_k]);
                }
            }
        }

        return $schema;
    }

    /**
    * params string cacheFolder
    */
    protected function setCache($cacheFolder)
    {
        if (extension_loaded('apc')) {
            $this->cache = new Cache\ApcCache();
        } else if (extension_loaded('xcache')) {
            $this->cache = new Cache\XcacheCache();
        } else if (extension_loaded('memcache')) {
            $memcache = new \Memcache();
            $memcache->connect('127.0.0.1');
            $this->cache = new Cache\MemcacheCache();
            $this->cache->setMemcache($memcache);
        } else if (extension_loaded('redis')) {
            $redis = new \Redis();
            $redis->connect('127.0.0.1');
            $this->cache = new Cache\RedisCache();
            $this->cache->setRedis($redis);
        } else if (null !== $cacheFolder) {
            $this->cache = new Cache\PhpFileCache($cacheFolder);
        } else {
            $this->cache = new Cache\ArrayCache();
        }

        $this->cache->setNamespace(self::CACHE_NAMESPACE);

        return $this;
    }

    protected function getCacheId()
    {
        return self::CACHE_ID.Namer::toLowercase($this->context->getProjectName());
    }
}

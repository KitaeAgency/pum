<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

use Pum\Bundle\CoreBundle\PumContext;
use Pum\Core\Extension\Util\Namer;
use Doctrine\Common\Cache;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
 */
class Search implements SearchInterface
{
    const SEARCH_ALL      = 'all';
    const RESPONSE_FORMAT = 'JSON';
    const DEFAULT_LIMIT   = 25;

    const CACHE_NAMESPACE = 'pum_schema';
    const CACHE_ID        = 'project_';
    const CACHE_TTL       = 3600;

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

    public function count($q, $objectName, $responseType)
    {
        $schema = $this->getSchema();

        var_dump($schema);
        die;
    }

    public function search($q, $objectName, $page, $limit, $responseType)
    {

    }

        /**
    * {@inheritDoc}
    */
    public function clearSchemaCache()
    {
        if (null !== $this->cache) {
            $this->cache->delete($this->getCacheId());
        }

        return $this;
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
        foreach ($project->getBeams() as $beam) {
            $schema[$beam->getName()] = array();
            foreach ($beam->getObjects() as $object) {
                $schema[$beam->getName()][$object->getName()] = array();
                foreach ($object->getFields() as $field) {
                    if (in_array($field->getType(), self::$searchableTypes)) {
                        $schema[$beam->getName()][$object->getName()][] = $field->getName();
                    }
                }

                if (empty($schema[$beam->getName()][$object->getName()])) {
                    unset($schema[$beam->getName()][$object->getName()]);
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

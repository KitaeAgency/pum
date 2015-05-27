<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * Class SearchApi
 * @package Pum\Bundle\WoodworkBundle\Extension\Search
 */
class Search implements SearchInterface
{
    const DEFAULT_LIMIT = 25;
    const SEARCH_ALL    = 'all';

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

    public function __construct(EntityManager $context, AuthorizationChecker $authorizationChecker, UrlGeneratorInterface $urlGenerator)
    {
        $this->em                   = $em;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator         = $urlGenerator;
    }

    public function count()
    {

    }

    public function search()
    {

    }
}

<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Search;

use Pum\Bundle\CoreBundle\PumContext;
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
    const DEFAULT_LIMIT   = 25;
    const RESPONSE_FORMAT = 'JSON';

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

    public function __construct(PumContext $context, AuthorizationChecker $authorizationChecker, UrlGeneratorInterface $urlGenerator)
    {
        $this->context              = $context;
        $this->authorizationChecker = $authorizationChecker;
        $this->urlGenerator         = $urlGenerator;
    }

    public function count($q, $objectName, $responseType)
    {

    }

    public function search($q, $objectName, $page, $limit, $responseType)
    {

    }
}

<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\WoodworkBundle\Extension\Search\SearchApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pum\Bundle\WoodworkBundle\Extension\Search\Search;

class SearchController extends Controller
{
    /**
     * @Route(path="/search/{type}", name="ww_search", defaults={"objectName"="all"})
     */
    public function searchAction(Request $request, $type = Search::SEARCH_TYPE_ALL)
    {
        $q         = $request->query->get('q');
        $searchApi = $this->get('woodwork.search.api');
        $res       = $searchApi->search($q, $type);

        return new JsonResponse($res);
    }
}

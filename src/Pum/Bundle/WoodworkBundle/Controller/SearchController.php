<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\WoodworkBundle\Extension\Search\SearchApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pum\Bundle\WoodworkBundle\Extension\Search\AbstractSearch;

class SearchController extends Controller
{
    /**
     * @Route(path="/search/{type}", name="ww_search")
     */
    public function searchAction(Request $request, $type = AbstractSearch::SEARCH_TYPE_ALL)
    {
        $q            = $request->query->get('q');
        $responseType = $request->query->get('type', 'JSON');
        $searchApi    = $this->get('woodwork.search.api');

        return $searchApi->search($q, $type, $responseType);
    }
}

<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\WoodworkBundle\Extension\Search\SearchApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    const ALL = 'all';

    /**
     * @Route(path="/search/{type}", name="ww_search")
     */
    public function searchAction(Request $request, $type = SearchApi::SEARCH_TYPE_ALL)
    {
        $q            = $request->query->get('q');
        $responseType = $request->query->get('type', 'JSON');
        $searchApi    = $this->get('woodwork.search');

        return $searchApi->search($q, $type, $responseType);
    }
}

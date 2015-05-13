<?php

namespace Pum\Bundle\WoodworkBundle\Controller;

use Pum\Bundle\WoodworkBundle\Extension\Search\SearchApi;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Pum\Bundle\WoodworkBundle\Extension\Search\Search;

class SearchController extends Controller
{
    /**
     * @Route(path="/search/{type}", name="ww_search")
     */
    public function searchAction(Request $request, $type = Search::SEARCH_TYPE_ALL)
    {
        return $this->get('woodwork.search.api')->search($request->query->get('q'), $type);
    }
}

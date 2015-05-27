<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Bundle\ProjectAdminBundle\Extension\Search\Search;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{
    /**
     * @Route(path="/{_project}/search_count", name="pa_search_count")
     */
    public function countAction(Request $request)
    {
        $q          = $request->query->get('q');
        $objectName = $request->query->get('objectName', Search::SEARCH_ALL);
        $searchApi  = $this->get('project.admin.search.api');

        return $searchApi->count($q, $objectName);
    }

    /**
     * @Route(path="/{_project}/search", name="pa_search")
     */
    public function searchAction(Request $request)
    {
        $q          = $request->query->get('q');
        $objectName = $request->query->get('objectName', Search::SEARCH_ALL);
        $page       = $request->query->get('page', 1);
        $limit      = $request->query->get('per_page', Search::DEFAULT_LIMIT);
        $searchApi  = $this->get('project.admin.search.api');

        return $searchApi->search($q, $objectName, $page, $limit);
    }
}

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
        $objectName = $request->query->get('objectName');
        $page       = $request->query->get('page', 1);
        $limit      = $request->query->get('per_page', $this->get('pum.config')->get('pa_default_pagination', Search::DEFAULT_LIMIT));
        $searchApi  = $this->get('project.admin.search.api');
        $objects    = $searchApi->search($q, $objectName, $page, $limit);

        var_dump($objects);
        exit;
    }
}

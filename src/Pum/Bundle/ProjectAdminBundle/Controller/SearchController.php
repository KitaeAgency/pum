<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Bundle\ProjectAdminBundle\Extension\Search\Search;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class SearchController extends Controller
{
    /**
     * @Route(path="/{_project}/search/{beamName}/{objectName}", name="pa_search", defaults={"beamName"=null, "objectName"=null})
     */
    public function searchAction(Request $request, $beamName, $objectName, $beam = null, $object = null)
    {
        $beam                    = $beamName ? $this->get('pum')->getBeam($beamName) : null;
        $objectDefinition        = $objectName ? $this->get('pum.context')->getProject()->getObject($objectName) : null;
        $searchApi               = $this->get('project.admin.search.api');
        list($template, $params) = $searchApi->search($request, $beam, $objectDefinition);

        // Render
        return $this->render($template, $params);
    }

    /**
     * @Route(path="/{_project}/search_clear_cache", name="pa_search_clear_cache")
     */
    public function searchClearCacheAction(Request $request)
    {
        $this->get('project.admin.search.api')->clearCache();

        return new JsonResponse('OK');
    }
}

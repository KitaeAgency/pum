<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Bundle\ProjectAdminBundle\Extension\Search\Search;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class SearchController extends Controller
{
    /**
     * @Route(path="/{_project}/search_count/{beamName}/{objectName}", name="pa_search_count", defaults={"beamName"=null, "objectName"=null})
     */
    public function countAction(Request $request, $beamName, $objectName, $beam = null, $object = null)
    {
        if ($beamName) {
            $beam = $this->get('pum')->getBeam($beamName);
        }

        if ($objectName) {
            $object = $this->get('pum.context')->getProject()->getObject($objectName);
        }

        return $this->render('PumProjectAdminBundle:Search:count.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $object,
            'results'           => $this->get('project.admin.search.api')->count($request->query->get('q'), $beamName, $objectName)
        ));
    }

    /**
     * @Route(path="/{_project}/search/{beamName}/{name}", name="pa_search", defaults={"beamName"="", "name"=""})
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function searchAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        $q          = $request->query->get('q');
        $objectName = $object->getName();
        $repository = $this->getRepository($objectName);
        $searchApi  = $this->get('project.admin.search.api');

        if (!$q) {
            return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $objectName)));
        }

        // Tableview stuff
        $tableView                                        = $this->getDefaultTableView($tableViewName = $request->query->get('view'), $beam, $object);
        $config_pa_default_tableview_truncatecols_value   = $this->get('pum.config')->get('pa_default_tableview_truncatecols_value');
        $config_pa_disable_default_tableview_truncatecols = $this->get('pum.config')->get('pa_disable_default_tableview_truncatecols');

        // Pagination stuff
        $page              = $request->query->get('page', 1);
        $per_page          = $request->query->get('per_page', $defaultPagination = $this->get('pum.config')->get('pa_default_pagination', Search::DEFAULT_LIMIT));
        $pagination_values = array_merge((array)$defaultPagination, $this->get('pum.config')->get('pa_pagination_values', array()));

        // Sort stuff
        $sortField = $tableView->getSortField($request->query->get('sort'));
        $sort      = $tableView->getSortColumnName($request->query->get('sort'));
        $order     = $tableView->getSortOrder($request->query->get('order'));

        if (!in_array($order, $orderTypes = array('asc', 'desc'))) {
            throw new \RuntimeException(sprintf('Invalid order value "%s". Available: "%s".', $order, implode(', ', $orderTypes)));
        }

        // Filters stuff
        $filters     = $request->query->has('filters') ? $tableView->combineValues($request->query->get('filters')) : $tableView->getFilters();
        $form_filter = $this->get('form.factory')->createNamed(null, 'pa_tableview', $tableView, array(
            'form_type'       => 'filters',
            'csrf_protection' => false,
            'with_submit'     => false,
            'attr'            => array('id' => 'form_filter', 'class' => 'cascade-fieldset'),
        ));

        if ($request->isMethod('POST') && $form_filter->submit($request)->isSubmitted()) {
            if ($response = $this->redirectFilters($form_filter->getData(), $request)) {
                return $response;
            }
        }

        // QB stuff
        $qb = $searchApi->search($q, $objectName, $page, $per_page);
        $qb = $repository->applyFilters($qb, $filters);
        $qb = $repository->applySort($qb, $sortField, $order);
        $qb = $this->get('pum.permission.entity_handle')->applyPermissions($qb, $object);

        // Pager stuff
        $adapter = new DoctrineORMAdapter($qb);
        $pager   = new Pagerfanta($adapter);
        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        // Count
        $count = $searchApi->count($q, $beam->getName(), $objectName);

        // Render
        return $this->render($this->container->getParameter('pum_pa.search.template'), array(
            'beam'                                             => $beam,
            'object_definition'                                => $object,
            'config_pa_default_tableview_truncatecols_value'   => $config_pa_default_tableview_truncatecols_value,
            'config_pa_disable_default_tableview_truncatecols' => $config_pa_disable_default_tableview_truncatecols,
            'table_view'                                       => $tableView,
            'pager'                                            => $pager,
            'count'                                            => $count,
            'pagination_values'                                => $pagination_values,
            'sort'                                             => $sort,
            'order'                                            => $order,
            'form_filter'                                      => $form_filter->createView(),
            'filters'                                          => $filters,
        ));
    }

    /**
     * @Route(path="/{_project}/search_clear_schema", name="pa_search_clear_schema")
     */
    public function searchClearSchemaAction(Request $request)
    {
        $this->get('project.admin.search.api')->clearSchemaCache();

        return new JsonResponse('OK');
    }
}

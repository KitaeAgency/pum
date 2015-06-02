<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Bundle\ProjectAdminBundle\Extension\Search\Search;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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
        $q                 = $request->query->get('q');
        $objectName        = $request->query->get('objectName');
        $object            = $this->get('pum')->getDefinition($this->get('pum.context')->getProjectName(), $objectName);
        $beam              = $object->getBeam();

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

        $qb = $this->get('project.admin.search.api')->search($q, $objectName, $page, $per_page);
        $qb = $this->get('pum.permission.entity_handle')->applyPermissions($qb, $object);

        // Pager stuff
        $adapter = new DoctrineORMAdapter($qb);
        $pager   = new Pagerfanta($adapter);

        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        // Render
        return $this->render($this->container->getParameter('pum_pa.search.template'), array(
            'beam'                                             => $beam,
            'object_definition'                                => $object,
            'config_pa_default_tableview_truncatecols_value'   => $config_pa_default_tableview_truncatecols_value,
            'config_pa_disable_default_tableview_truncatecols' => $config_pa_disable_default_tableview_truncatecols,
            'table_view'                                       => $tableView,
            'pager'                                            => $pager,
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

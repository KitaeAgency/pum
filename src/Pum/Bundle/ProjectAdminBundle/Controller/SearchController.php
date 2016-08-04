<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Bundle\ProjectAdminBundle\Extension\Search\Search;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class SearchController extends Controller
{
    /**
     * @Route(path="/{_project}/search/{beamName}/{objectName}", name="pa_search", defaults={"beamName"=null, "objectName"=null})
     */
    public function searchAction(Request $request, $beamName, $objectName)
    {
        $this->assertGranted('ROLE_PA_LIST');

        if (!$q = $request->query->get('q')) {
            if ($refefer = $this->getRequest()->headers->get('referer')) {
                return $this->redirect($refefer);
            }

            throw new \RuntimeException(sprintf("Your search cannot be null."));
        }

        $searchApi               = $this->get('project.admin.search.api');
        $beam                    = $beamName ? $this->get('pum')->getBeam($beamName) : null;
        $objectDefinition        = $objectName ? $this->get('pum.context')->getProject()->getObject($objectName) : null;
        list($template, $params) = $searchApi->search($q, $beam, $objectDefinition);
        $params                  = array_merge($params, array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
        ));

        /* Only execute for pum core search */
        if (null !== $objectDefinition && $searchApi->getName() == Search::SEARCH_NAME) {
            // Tableview stuff
            $tableView = $this->getDefaultTableView($tableViewName = $request->query->get('view'), $beam, $objectDefinition);

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

            if ($pager = (isset($params['qb']) && $params['qb'] instanceof QueryBuilder)) {
                $repository = $this->getRepository($objectName);
                $qb         = $params['qb'];
                $qb         = $repository->applyFilters($qb, $filters);
                $qb         = $repository->applySort($qb, $sortField, $order);
                $qb         = $this->get('pum.permission.entity_handle')->applyPermissions($qb, $objectDefinition);

                // Pager stuff
                $adapter = new DoctrineORMAdapter($qb);
                $pager   = new Pagerfanta($adapter);
                $pager->setMaxPerPage($per_page);
                $pager->setCurrentPage($page);

                unset($params['qb']);
            }

            $params = array_merge($params, array(
                'pager'                                            => $pager,
                'config_pa_default_tableview_truncatecols_value'   => $this->get('pum.config')->get('pa_default_tableview_truncatecols_value'),
                'config_pa_disable_default_tableview_truncatecols' => $this->get('pum.config')->get('pa_disable_default_tableview_truncatecols'),
                'table_view'                                       => $tableView,
                'pagination_values'                                => $pagination_values,
                'sort'                                             => $sort,
                'order'                                            => $order,
                'form_filter'                                      => $form_filter->createView(),
                'filters'                                          => $filters
            ));
        }

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

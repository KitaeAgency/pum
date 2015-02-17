<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Pum\Core\Events;
use Pum\Core\Event\ObjectDefinitionEvent;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\TableView;
use Pum\Core\Definition\View\ObjectView;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class ObjectController extends Controller
{
    const DEFAULT_PAGINATION = 10;

    /**
     * @Route(path="/{_project}/search/{beamName}/{name}/regenerate-index", name="pa_object_regenerate_index")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function regenerateIndexAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        if ($object->isSearchEnabled()) {
            $object->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($object));
            $this->get('pum')->saveBeam($beam);
            $this->addSuccess('Search index successfully reindexed');
        } else {
            $this->addSuccess('This is not a searchable object');
        }

        return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $object->getname())));
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}", name="pa_object_list")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("object", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function listAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        $this->assertGranted('PUM_OBJ_VIEW', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $object->getName(),
        ));

        if (false === $object->isTreeEnabled() || null === $object->getTree()) {
            return $this->listRegularObjectAction($request, $beam, $object);
        }

        return $this->listTreeObjectAction($request, $beam, $object);
    }

    private function listRegularObjectAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        // Config stuff
        $config = $this->get('pum.config');

        // TableView stuff
        $tableView = $this->getDefaultTableView($tableViewName = $request->query->get('view'), $beam, $object);

        $config_pa_default_tableview_truncatecols_value = $config->get('pa_default_tableview_truncatecols_value');
        $config_pa_disable_default_tableview_truncatecols = $config->get('pa_disable_default_tableview_truncatecols');

        // Pagination stuff
        $page              = $request->query->get('page', 1);
        $per_page          = $request->query->get('per_page', $defaultPagination = $config->get('pa_default_pagination', self::DEFAULT_PAGINATION));
        $pagination_values = array_merge((array)$defaultPagination, $config->get('pa_pagination_values', array()));

        if (!in_array($per_page, $pagination_values)) {
            throw new \RuntimeException(sprintf('Invalid pagination value "%s". Available: "%s".', $per_page, implode('-', $pagination_values)));
        }

        // Sort stuff
        $sortField = $tableView->getSortField($request->query->get('sort'));
        $sort      = $tableView->getSortColumnName($request->query->get('sort'));
        $order     = $tableView->getSortOrder($request->query->get('order'));

        if (!in_array($order, $orderTypes = array('asc', 'desc'))) {
            throw new \RuntimeException(sprintf('Invalid order value "%s". Available: "%s".', $order, implode(', ', $orderTypes)));
        }

        // Filters stuff
        $filters = $request->query->has('filters') ? $tableView->combineValues($request->query->get('filters')) : $tableView->getFilters();

        $form_filter = $this->get('form.factory')->createNamed(null, 'pa_tableview', $tableView, array(
            'form_type'       => 'filters',
            'csrf_protection' => false,
            'with_submit'     => false,
            'attr'            => array('id' => 'form_filter', 'class' => 'cascade-fieldset'),
        ));

        if ($request->isMethod('POST') && $form_filter->handleRequest($request)->isSubmitted()) {
            if ($response = $this->redirectFilters($form_filter->getData(), $request)) {
                return $response;
            }
        }

        // Render
        return $this->render('PumProjectAdminBundle:Object:list.html.twig', array(
            'beam'                                              => $beam,
            'object_definition'                                 => $object,
            'config_pa_default_tableview_truncatecols_value'    => $config_pa_default_tableview_truncatecols_value,
            'config_pa_disable_default_tableview_truncatecols'  => $config_pa_disable_default_tableview_truncatecols,
            'table_view'                                        => $tableView,
            'pager'                                             => $this->get('pum.context')->getProjectOEM()->getRepository($object->getName())->getPage($page, $per_page, $sortField, $order, $filters),
            'pagination_values'                                 => $pagination_values,
            'sort'                                              => $sort,
            'order'                                             => $order,
            'form_filter'                                       => $form_filter->createView(),
            'filters'                                           => $filters,
        ));
    }

    private function listTreeObjectAction(Request $request, Beam $beam, ObjectDefinition $object)
    {
        if (null === $treeField = $object->getTree()->getTreeField()) {
            throw new \RuntimeException('No tree field defined for the object');
        }

        $labelField = $object->getTree()->getLabelField();

        // Render
        return $this->render('PumProjectAdminBundle:Object:tree.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $object,
            'cookie_namespace'  => $this->get('pum_core.tree.api')->getTreeNamespace($object, $treeField->getName())
        ));
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/create", name="pa_object_create")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function createAction(Request $request, Beam $beam, $name, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('PUM_OBJ_CREATE', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
        ));

        $oem      = $this->get('pum.context')->getProjectOEM();
        $object   = $oem->createObject($name);
        $formView = $this->getDefaultFormView($formViewName = $request->query->get('view'), $objectDefinition);
        $isAjax   = $request->isXmlHttpRequest();

        $parent = $request->query->get('parent_id', null);
        if ('#' == $parent || 'root' == $parent) {
            $parent = null;
        }

        $form = $this->createForm('pum_object', $object, array(
            'attr' => array(
                'class'            => $isAjax ? 'yaah-js pum_create' : null,
                'data-ya-trigger'  => $isAjax ? 'submit' : null,
                'data-ya-location' => $isAjax ? 'inner' : null,
                'data-ya-target'   => $isAjax ? '#pumAjaxModal .modal-content' : null,
                'data-parent'      => $isAjax ? $request->query->get('parent_id', null) : null
            ),
            'action' => $this->generateUrl('pa_object_create', array(
                'beamName'  => $beam->getName(),
                'name'      => $objectDefinition->getName(),
                'parent_id' => $request->query->get('parent_id', null)
            )),
            'form_view' => $formView
        ));

        if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
            return $response;
        }

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            if ($parent) {
                if (false === $objectDefinition->isTreeEnabled() || null === $tree = $objectDefinition->getTree()) {
                    throw new \RuntimeException($object->getName().' is not treeable');
                }

                if (null === $treeField = $tree->getTreeField()) {
                    throw new \RuntimeException('No tree field defined for the object '.$objectDefinition->getName());
                }

                $parentSetter = 'set'.ucfirst(Namer::toCamelCase($treeField->getTypeOption('inversed_by')));

                if (null !== $parent = $oem->getRepository($objectDefinition->getName())->find($parent)) {
                    $object->$parentSetter($parent);
                }
            }

            $oem->persist($object);
            $oem->flush();

            if (!$isAjax) {
                $this->addSuccess('Object successfully created');
            }

            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $object->getId(), 'view' => $formView->getName())));
        }

        $params = array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $object,
        );

        if ($request->isXmlHttpRequest()) {
            return $this->render('PumProjectAdminBundle:Object:create.ajax.html.twig', $params);
        }

        return $this->render('PumProjectAdminBundle:Object:create.html.twig', $params);
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/{id}/edit", name="pa_object_edit")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function editAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('PUM_OBJ_EDIT', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
            'id' => $id,
        ));

        $this->throwNotFoundUnless($object = $this->getRepository($name)->find($id));

        $objectView = clone $object;
        $oem        = $this->get('pum.context')->getProjectOEM();
        $formView   = $this->getDefaultFormView($formViewName = $request->query->get('view'), $objectDefinition);
        $cm         = $this->get('pum.object.collection.manager');
        $params     = array();
        $isAjax     = $request->isXmlHttpRequest();

        list($nbTab, $regularTab, $activeTab, $routingTab, $requestField) = $this->getParameters($formView, $objectDefinition, $request);

        /* Regular Fields */
        if (null === $activeTab && false == $routingTab && $regularTab) {
            $form = $this->createForm('pum_object', $object, array(
                'attr' => array(
                    'class'            => $isAjax ? 'yaah-js pum_edit' : null,
                    'data-ya-trigger'  => $isAjax ? 'submit' : null,
                    'data-ya-location' => $isAjax ? 'inner' : null,
                    'data-ya-target'   => $isAjax ? '#pumAjaxModal .modal-content' : null,
                    'data-node-id'     => $isAjax ? $object->getId() : null
                ),
                'action' => $this->generateUrl('pa_object_edit', array(
                    'beamName' => $beam->getName(),
                    'name'     => $objectDefinition->getName(),
                    'id'       => $id
                )),
                'form_view'   => $formView,
                'subscribers' => new \Pum\Bundle\TypeExtraBundle\Listener\MediaDeleteListener()
            ));

            if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
                return $response;
            }

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $oem->persist($object);
                $oem->flush();

                if (!$isAjax) {
                    $this->addSuccess('Object successfully updated');
                }

                return $this->redirect($this->generateUrl(
                    'pa_object_edit',
                    array(
                        'beamName' => $beam->getName(),
                        'name' => $name, 'id' => $id,
                        'view' => $formView->getName()
                    )
                ));
            }

            $params = array('form' => $form->createView());

        /* Relations Field */
        } elseif (null !== $activeTab) {
            /* Add/Remove Method */
            $config = $this->get('pum.config');
            $return = new Response('OK');

            if (in_array($request->query->get('action'), array('removeselected', 'removeall', 'add', 'set'))) {
                $return = $this->redirect($this->generateUrl(
                    'pa_object_edit',
                    array(
                        'beamName' => $beam->getName(),
                        'name' => $name, 'id' => $id,
                        'view' => $formView->getName(),
                        'tab' => $activeTab
                    )
                ));
            }

            /* Handle Ajax Request */
            if ($response = $cm->handleRequest($request, $object, $requestField->getField(), $return)) {
                return $response;
            }

            $multiple          = in_array($requestField->getField()->getTypeOption('type'), array('one-to-many', 'many-to-many'));
            $page              = $request->query->get('page', 1);
            $per_page          = $request->query->get('per_page', $defaultPagination = $config->get('pa_default_pagination', self::DEFAULT_PAGINATION));
            $sort              = $request->query->get('sort', 'id');
            $order             = $request->query->get('order', 'asc');
            $pagination_values = array_merge((array)$defaultPagination, $config->get('pa_pagination_values', array()));

            if (!in_array($order, $orderTypes = array('asc', 'desc'))) {
                throw new \RuntimeException(sprintf('Invalid order value "%s". Available: "%s".', $order, implode(', ', $orderTypes)));
            }

            $tableview = null;
            $field     = $requestField->getField()->getTypeOption('target');
            if ($tableviewname = $requestField->getOption('tableview')) {
                if ($beam->hasObject($field)) {
                    $relationDefinition = $beam->getObject($field);
                    if ($relationDefinition->hasTableView($tableviewname)) {
                        $tableview = $relationDefinition->getTableView($tableviewname);
                    }
                }
            }

            $params = array(
                'pagination_values' => $pagination_values,
                'property'          => $requestField->getOption('property'),
                'tableview'         => $tableview,
                'field'             => $field,
                'sort'              => $sort,
                'order'             => $order,
                'target'            => $requestField->getField()->getTypeOption('type'),
                'inversed_by'       => $requestField->getField()->getTypeOption('inversed_by'),
                'relation_type'     => $requestField->getField()->getTypeOption('type'),
                'allow_add'         => $requestField->getOption('allow_add'),
                'allow_delete'      => $requestField->getOption('allow_delete'),
                'multiple'          => $multiple,
                'maxtags'           => $multiple ? 0 : 1,
            );

            $pager = $cm->getItems($object, $requestField->getField(), $page, $per_page, array($sort => $order));
            if ($multiple) {
                $params['pager'] = $pager;
            } else {
                $params['pager'] = (null === $pager) ? array() : array($pager);
            }

        } elseif ($routingTab) {
            $form = $this->createForm('pum_object_routing', $object, array(
                'routing_object' => $object
            ));

            if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                $oem->persist($object);
                $oem->flush();
                $this->addSuccess('Object successfully updated');

                return $this->redirect($this->generateUrl('pa_object_edit', array(
                    'beamName' => $beam->getName(),
                    'name'     => $name, 'id' => $id,
                    'view'     => $formView->getName(),
                    'routing'  => true
                )));
            }

            $params = array('form' => $form->createView());
        }

        $params = array_merge($params, array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'object'            => $objectView,
            'formView'          => $formView,
            'activeTab'         => $activeTab,
            'regularTab'        => $regularTab,
            'routingTab'        => $routingTab,
            'nbTab'             => $nbTab,
            'cm'                => $cm,
        ));

        // if ($request->isXmlHttpRequest()) {
        //     return $this->render('PumProjectAdminBundle:Object:edit.ajax.html.twig', $params);
        // }

        return $this->render('PumProjectAdminBundle:Object:edit.html.twig', $params);
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/{id}/delete", name="pa_object_delete")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteAction(Request $request, Beam $beam, $name, $id)
    {
        $this->assertGranted('PUM_OBJ_DELETE', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
            'id' => $id,
        ));

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $oem->remove($object);
        $oem->flush();
        $this->addSuccess('Object successfully deleted');

        return $this->redirect($this->generateUrl('pa_object_list', array_merge($request->query->all(), array('beamName' => $beam->getName(), 'name' => $name))));
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/deletelist", name="pa_object_delete_list")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteListAction(Request $request, Beam $beam, $name)
    {
        $this->assertGranted('PUM_OBJ_DELETE', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
        ));

        if ($request->request->has('entities')) {
            $oem = $this->get('pum.context')->getProjectOEM();
            $repository = $oem->getRepository($name);

            foreach ($request->request->get('entities') as $id) {
                $this->throwNotFoundUnless($object = $repository->find($id));
                $oem->remove($object);
            }

            $oem->flush();
            $this->addSuccess('Objects successfully deleted');
        }

        return $this->redirect($this->generateUrl('pa_object_list', array_merge($request->query->all(), array('beamName' => $beam->getName(), 'name' => $name))));
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/{id}/clone", name="pa_object_clone")
     * @ParamConverter("beam", class="Beam")
     */
    public function cloneAction(Request $request, Beam $beam, $name, $id)
    {
        $this->assertGranted('PUM_OBJ_EDIT', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
            'id' => $id,
        ));

        $oem                               = $this->get('pum.context')->getProjectOEM();
        $repository                        = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        $objectView                        = clone $object;
        $formView                          = $beam->getObject($name)->createDefaultFormView();

        if ($request->isMethod('POST')) {
            $newObject = $oem->createObject($name);
            $form = $this->createForm('pum_object', $newObject, array(
                'form_view' => $formView
            ));
            if ($form->handleRequest($request)->isValid()) {
                $oem->persist($newObject);
                $oem->flush();
                $this->addSuccess('Object successfully cloned');

                return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $name)));
            }
        } else {
            $form = $this->createForm('pum_object', $object, array(
                'form_view' => $formView
            ));
        }

        return $this->render('PumProjectAdminBundle:Object:clone.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $objectView,
        ));
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/deleteall", name="pa_object_deleteall")
     * @ParamConverter("beam", class="Beam")
     */
    public function deleteallAction(Request $request, Beam $beam, $name)
    {
        $this->assertGranted('PUM_OBJ_DELETE', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
        ));

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        foreach ($repository->findAll() as $object) {
            $oem->remove($object);
        }

        $oem->flush();
        $this->addSuccess('Objects successfully deleted');

        return $this->redirect($this->generateUrl('pa_object_list', array('beamName' => $beam->getName(), 'name' => $name)));
    }

    /**
     * @Route(path="/{_project}/object/{beamName}/{name}/{id}/view", name="pa_object_view")
     * @ParamConverter("beam", class="Beam")
     * @ParamConverter("objectDefinition", class="ObjectDefinition", options={"objectDefinitionName" = "name"})
     */
    public function viewAction(Request $request, Beam $beam, $name, $id, ObjectDefinition $objectDefinition)
    {
        $this->assertGranted('PUM_OBJ_VIEW', array(
            'project' => $this->get('pum.context')->getProject()->getName(),
            'beam' => $beam->getName(),
            'object' => $name,
            'id' => $id,
        ));

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));

        $objectViewName = $request->query->get('view');
        if ($objectViewName === null || $objectViewName === ObjectView::DEFAULT_NAME || $objectViewName === '') {
            if ($objectViewName === ObjectView::DEFAULT_NAME || null === $objectView = $objectDefinition->getDefaultObjectView()) {
                $objectView = $objectDefinition->createDefaultObjectView();
            }
        } else {
            try {
                $objectView = $objectDefinition->getObjectView($objectViewName);
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Object view not found.', $e);
            }
        }

        return $this->render('PumProjectAdminBundle:Object:view.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'object'            => $object,
            'object_view'       => $objectView,
        ));
    }

    /*
     * Redirecting to filters query
     */
    private function redirectFilters(TableView $tableView, Request $request)
    {
        $filtersColumnCollection = $tableView->getFilters();

        $queryFilters = array();
        foreach ($filtersColumnCollection as $filters) {
            foreach ($filters['filters'] as $filter) {
                $queryFilters[$filters['key']][] = array(
                    'type'  => $filter->getType(),
                    'value' => $filter->getValue()
                );
            }
        }

        $query = array_merge($request->query->all(), array('page' => null, 'filters' => $queryFilters));
        krsort($query);

        $url = $request->getBaseUrl().$request->getPathInfo().'?'.http_build_query($query);

        return $this->redirect($url);
    }

    /*
     * Return TableView
     * Throw createNotFoundException
     */
    private function getDefaultTableView($tableViewName, Beam $beam, ObjectDefinition $object)
    {
        if (TableView::DEFAULT_NAME === $tableViewName) {
            return $object->createDefaultTableView();
        }

        if ($tableViewName === null || $tableViewName === '') {
            if (null !== $tableView = $this->getUser()->getPreferredTableView($this->get('pum.context')->getProject(), $beam, $object)) {
                return $tableView;
            }
            if (null !== $tableView = $object->getDefaultTableView()) {
                return $tableView;
            }

            return $object->createDefaultTableView();

        } else {
            try {
                $tableView = $object->getTableView($tableViewName);

                return $tableView;
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Table view not found.', $e);
            }
        }
    }

    /*
     * Return FormView
     * Throw createNotFoundException
     */
    private function getDefaultFormView($formViewName, ObjectDefinition $object)
    {
        if (FormView::DEFAULT_NAME === $formViewName) {
            return $object->createDefaultFormView();
        }

        if ($formViewName === null || $formViewName === '') {
            if (null !== $formView = $object->getDefaultFormView()) {
                return $formView;
            }

            return $object->createDefaultFormView();

        } else {
            try {
                $formView = $object->getFormView($formViewName);

                return $formView;
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Form view not found.', $e);
            }
        }
    }

    /*
     * Return FormView
     */
    private function getParameters(FormView $formView, ObjectDefinition $object, Request $request)
    {
        $nbTab        = 0;
        $regularTab   = false;
        $activeTab    = null;
        $routingTab   = false;
        $requestField = null;
        $requestTab   = $request->query->get('tab');

        /* Resolve tabs */
        foreach ($formView->getFields() as $field) {
            if (null !== $field->getOption('form_type') && $field->getOption('form_type') == 'tab') {
                $nbTab++;
                if ($field->getLabel() == $requestTab) {
                    $activeTab    = $requestTab;
                    $requestField = $field;
                }
            } else {
                $regularTab = true;
            }
        }

        /* Active routing atb */
        if ($object->isSeoEnabled() && $this->container->get('security.context')->isGranted('ROLE_PA_ROUTING')) {
            $nbTab++;
            $routingTab = $request->query->get('routing') ? true : false;
        }

        /* Autoselect first tab */
        if (false === $regularTab && false === $routingTab && null === $activeTab && $nbTab > 0) {
            foreach ($formView->getFields() as $field) {
                if (null !== $field->getOption('form_type') && $field->getOption('form_type') == 'tab') {
                    $activeTab    = $field->getLabel();
                    $requestField = $field;

                    break;
                }
            }
        }

        return array($nbTab, $regularTab, $activeTab, $routingTab, $requestField);
    }
}

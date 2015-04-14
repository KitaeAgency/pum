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
use Pum\Core\Relation\Relation;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomViewRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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

    protected function listRegularObjectAction(Request $request, Beam $beam, ObjectDefinition $object, array $additionalFilters = array())
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
        $filters = array_merge($filters, $additionalFilters);

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

        $qb = $this->get('pum.context')->getProjectOEM()->getRepository($object->getName())->getPageQuery($sortField, $order, $filters);
        $qb = $this->get('pum.permission.entity_handle')->applyPermissions($qb, $object);

        $adapter = new DoctrineORMAdapter($qb);
        $pager   = new Pagerfanta($adapter);

        $pager->setMaxPerPage($per_page);
        $pager->setCurrentPage($page);

        // Render
        return $this->render('PumProjectAdminBundle:Object:list.html.twig', array(
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

    protected function listTreeObjectAction(Request $request, Beam $beam, ObjectDefinition $object)
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

        $tableView = $this->getDefaultTableView($request->query->get('view'), $beam, $objectDefinition);
        $formView = $tableView->getPreferredFormCreateView();

        if (!$formView) {
            $formView = $tableView->getPreferredFormView();
        }

        if (($formViewName = $request->query->get('formview')) || !$formView) {
            $formView = $this->getDefaultFormView($formViewName, $objectDefinition, FormView::TYPE_CREATE);
        }

        $oem      = $this->get('pum.context')->getProjectOEM();
        $object   = $oem->createObject($name);
        $isAjax   = $request->isXmlHttpRequest();
        $fromUrl  = $request->query->get('fromUrl', null);

        list($formView) = $this->getCreateParameters($request, $formView, $objectDefinition);

        $form = $this->createForm('pum_object', $object, array(
            'attr' => array(
                'class'            => $isAjax ? 'yaah-js pum_create' : null,
                'data-ya-trigger'  => $isAjax ? 'submit' : null,
                'data-ya-location' => $isAjax ? 'inner' : null,
                'data-ya-target'   => $isAjax ? '#pumAjaxModal .modal-content' : null,
                'data-parent'      => $isAjax ? $request->query->get('parent_id', null) : null
            ),
            'action' => $this->generateUrl('pa_object_create', array_merge($request->query->all(), array(
                'beamName'  => $beam->getName(),
                'name'      => $objectDefinition->getName(),
            ))),
            'form_view'       => $formView,
            'dispatch_events' => true,
        ));

        if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
            return $response;
        }

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $parent = $request->query->get('parent_id', null);
            if ('#' == $parent || 'root' == $parent) {
                $parent = null;
            }

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

            } else {
                $relationObjectName = $request->query->get('relationObject', null);
                $relationId         = $request->query->get('relationId', null);
                $relationType       = $request->query->get('relationType', null);

                if (in_array($relationType, array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY))) {
                    $relationSetter = 'add'.ucfirst(Namer::getSingular(Namer::toCamelCase($request->query->get('relationName', null))));
                } else {
                    $relationSetter = 'set'.ucfirst(Namer::toCamelCase($request->query->get('relationName', null)));
                }

                if ($relationObjectName && $relationId) {
                    $this->throwNotFoundUnless($relationObject = $this->getRepository($relationObjectName)->find($relationId));
                    $object->$relationSetter($relationObject);
                }
            }

            $oem->persist($object);
            $oem->flush();

            $this->addSuccess('Object successfully created');

            if ($fromUrl) {
                return $this->redirect($fromUrl);
            }

            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $object->getId(), 'view' => $formViewName)));
        }

        $params = array(
            'fromUrl'           => $fromUrl,
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $object,
            'formView'          => $formView,
            'activeTab'         => 'regularFields',
            'chosenTabType'     => 'regularFields',
            'hasRouting'        => false
        );

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

        $tableView = $this->getDefaultTableView($request->query->get('view'), $beam, $objectDefinition);
        $formView = $tableView->getPreferredFormView();

        if (($formViewName = $request->query->get('formview')) || !$formView) {
            $formView = $this->getDefaultFormView($formViewName, $objectDefinition);
        }

        $params        = array();
        $objectView    = clone $object;
        $requestTab    = $request->query->get('tab');
        $isAjax        = $request->isXmlHttpRequest();
        $entityManager = $this->getDoctrine()->getManager();
        $cm            = $this->get('pum.object.collection.manager');
        $oem           = $this->get('pum.context')->getProjectOEM();

        $entityManager->detach($formView);

        list($chosenTab, $chosenTabType, $formView, $relationField, $hasRouting) = $this->getEditParameters($request, $formView, $objectDefinition);

        switch ($chosenTabType) {
            case 'regularFields':
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
                        'id'       => $id,
                        'formview' => $formViewName,
                        'tab'      => $requestTab
                    )),
                    'form_view'       => $formView,
                    'dispatch_events' => true,
                    'subscribers'     => new \Pum\Bundle\TypeExtraBundle\Listener\MediaDeleteListener()
                ));

                if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
                    return $response;
                }

                if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
                    $oem->persist($object);
                    $oem->flush();

                    $this->addSuccess('Object successfully updated');

                    return $this->redirect($this->generateUrl('pa_object_edit', array(
                        'beamName' => $beam->getName(),
                        'name'     => $name,
                        'id'       => $id,
                        'formview' => $formViewName,
                        'tab'      => $requestTab
                    )));
                }

                $params = array('form' => $form->createView());
                break;

            case 'relationFields':
                $config = $this->get('pum.config');
                $return = new Response('OK');

                if (in_array($request->query->get('action'), array('removeselected', 'removeall', 'add', 'set'))) {
                    $return = $this->redirect($this->generateUrl(
                        'pa_object_edit',
                        array(
                            'beamName' => $beam->getName(),
                            'name'     => $name,
                            'id'       => $id,
                            'formview' => $formViewName,
                            'tab'      => $requestTab
                        )
                    ));
                }

                /* Handle Ajax Request */
                if ($response = $cm->handleRequest($request, $object, $relationField->getField(), $return)) {
                    return $response;
                }

                $page              = $request->query->get('page', 1);
                $per_page          = $request->query->get('per_page', $defaultPagination = $config->get('pa_default_pagination', self::DEFAULT_PAGINATION));
                $sort              = $request->query->get('sort', 'id');
                $sortField         = $request->query->get('sort_field', 'id');
                $order             = $request->query->get('order', 'asc');
                $pagination_values = array_merge((array)$defaultPagination, $config->get('pa_pagination_values', array()));

                if (!in_array($order, $orderTypes = array('asc', 'desc'))) {
                    throw new \RuntimeException(sprintf('Invalid order value "%s". Available: "%s".', $order, implode(', ', $orderTypes)));
                }

                $tableview  = null;
                $field      = $relationField->getField()->getTypeOption('target');
                $targetBeam = $this->get('pum')->getBeam($relationField->getField()->getTypeOption('target_beam'));

                if ($tableviewname = $relationField->getOption('tableview')) {
                    if ($targetBeam->hasObject($field)) {
                        $relationDefinition = $targetBeam->getObject($field);
                        if ($relationDefinition->hasTableView($tableviewname)) {
                            $tableview = $relationDefinition->getTableView($tableviewname);

                            if (null !== $_sortField = $tableview->getSortField($sort)) {
                                $sortField = $_sortField->getName();
                            }
                        }
                    }
                }

                $params = array(
                    'pagination_values'   => $pagination_values,
                    'property'            => $relationField->getOption('property', 'id'),
                    'tableview'           => $tableview,
                    'field'               => $relationField->getField(),
                    'sort'                => $sort,
                    'order'               => $order,
                    'reverseRelationType' => Relation::getInverseType($relationField->getField()->getTypeOption('type')),
                    'multiple'            => $multiple = in_array($relationField->getField()->getTypeOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY)),
                    'allow_add'           => $relationField->getOption('allow_add'),
                    'allow_delete'        => $relationField->getOption('allow_delete'),
                    'maxtags'             => $multiple ? 0 : 1,
                );

                $pager = $cm->getItems($object, $relationField->getField(), $page, $per_page, array($sortField => $order));
                if ($multiple) {
                    $params['pager'] = $pager;
                } else {
                    $params['pager'] = (null === $pager) ? array() : array($pager);
                }
                break;

            case 'routing':
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
                        'view'     => $formViewName,
                        'tab'      => $requestTab
                    )));
                }

                $params = array('form' => $form->createView());
                break;

            default:
                throw new \RuntimeException('Wrong parameters');
                break;
        }

        $params = array_merge($params, array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'object'            => $objectView,
            'formView'          => $formView,
            'cm'                => $cm,
            'activeTab'         => $chosenTab,
            'chosenTabType'     => $chosenTabType,
            'hasRouting'        => $hasRouting
        ));

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

        $this->throwNotFoundUnless($object = $this->getRepository($name)->find($id));

        $tableView = $this->getDefaultTableView($request->query->get('view'), $beam, $objectDefinition);
        $objectView = $tableView->getPreferredObjectView();

        if (($objectViewName = $request->query->get('objectview')) || !$objectView) {
            $objectView = $this->getDefaultObjectView($objectViewName, $objectDefinition);
        }

        $params        = array();
        $requestTab    = $request->query->get('tab');
        $isAjax        = $request->isXmlHttpRequest();
        $cm            = $this->get('pum.object.collection.manager');

        list($chosenTab, $chosenTabType, $objectView, $relationField, $hasRouting) = $this->getViewParameters($request, $objectView, $objectDefinition);

        switch ($chosenTabType) {
            case 'relationFields':
                $config            = $this->get('pum.config');
                $page              = $request->query->get('page', 1);
                $per_page          = $request->query->get('per_page', $defaultPagination = $config->get('pa_default_pagination', self::DEFAULT_PAGINATION));
                $sort              = $request->query->get('sort', 'id');
                $sortField         = $request->query->get('sort_field', 'id');
                $order             = $request->query->get('order', 'asc');
                $pagination_values = array_merge((array)$defaultPagination, $config->get('pa_pagination_values', array()));

                if (!in_array($order, $orderTypes = array('asc', 'desc'))) {
                    throw new \RuntimeException(sprintf('Invalid order value "%s". Available: "%s".', $order, implode(', ', $orderTypes)));
                }

                $tableview  = null;
                $field      = $relationField->getField()->getTypeOption('target');
                $targetBeam = $this->get('pum')->getBeam($relationField->getField()->getTypeOption('target_beam'));

                if ($tableviewname = $relationField->getOption('tableview')) {
                    if ($targetBeam->hasObject($field)) {
                        $relationDefinition = $targetBeam->getObject($field);
                        if ($relationDefinition->hasTableView($tableviewname)) {
                            $tableview = $relationDefinition->getTableView($tableviewname);

                            if (null !== $_sortField = $tableview->getSortField($sort)) {
                                $sortField = $_sortField->getName();
                            }
                        }
                    }
                }

                if (null === $tableview) {
                    $sortField = $request->query->get('sort_field', $sort);
                }

                $params = array(
                    'pagination_values'   => $pagination_values,
                    'property'            => $relationField->getOption('property', 'id'),
                    'tableview'           => $tableview,
                    'field'               => $relationField->getField(),
                    'sort'                => $sort,
                    'order'               => $order,
                    'multiple'            => $multiple = in_array($relationField->getField()->getTypeOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY)),
                );

                $pager = $cm->getItems($object, $relationField->getField(), $page, $per_page, array($sortField => $order));
                if ($multiple) {
                    $params['pager'] = $pager;
                } else {
                    $params['pager'] = (null === $pager) ? array() : array($pager);
                }
                break;
        }

        $params = array_merge($params, array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'object'            => $object,
            'objectView'        => $objectView,
            'cm'                => $cm,
            'activeTab'         => $chosenTab,
            'chosenTabType'     => $chosenTabType,
            'hasRouting'        => $hasRouting
        ));

        return $this->render('PumProjectAdminBundle:Object:view.html.twig', $params);
    }

    /*
     * Redirecting to filters query
     */
    protected function redirectFilters(TableView $tableView, Request $request)
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
    protected function getDefaultTableView($tableViewName, Beam $beam, ObjectDefinition $object)
    {
        if ($tableViewName === null || $tableViewName === '') {
            $tableView = $this->get('pum.customview_repository')->getPreferredTableView(
                $this->getUser(),
                $this->get('pum.context')->getProject(),
                $beam,
                $object
            );

            if (null !== $tableView) {
                return $tableView;
            }
            return $object->getDefaultTableView();
        } else {
            try {
                $tableView = $this->get('pum.customview_repository')->getPreferredTableView(
                    $this->getUser(),
                    $this->get('pum.context')->getProject(),
                    $beam,
                    $object,
                    $tableViewName
                );

                return $tableView;
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Table view not found.', $e);
            }
        }
    }

    /*
     * Return ObjectView
     * Throw createNotFoundException
     */
    protected function getDefaultObjectView($objectViewName, ObjectDefinition $object)
    {
        if (ObjectView::DEFAULT_NAME === $objectViewName) {
            return $object->createDefaultObjectView();
        }

        if ($objectViewName === null || $objectViewName === '') {
            return $object->getDefaultObjectView();
        } else {
            try {
                $objectView = $object->getObjectView($objectViewName);

                return $objectView;
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Object view not found.', $e);
            }
        }
    }

    /*
     * Return FormView
     * Throw createNotFoundException
     */
    protected function getDefaultFormView($formViewName, ObjectDefinition $object, $type = null)
    {
        if (FormView::DEFAULT_NAME === $formViewName) {
            return $object->createDefaultFormView();
        }

        if ($formViewName === null || $formViewName === '') {
            if ($type === FormView::TYPE_CREATE) {
                return $object->getDefaultFormCreateView();
            }
            return $object->getDefaultFormEditView();
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
     * Return Array
     */
    protected function getViewParameters(Request $request, ObjectView $objectView, ObjectDefinition $object)
    {
        $hasRouting = $object->isSeoEnabled() && $this->container->get('security.context')->isGranted('ROLE_PA_ROUTING');

        if (null !== $chosenTab = $request->query->get('tab')) {
            if ($hasRouting && 'routing' == $chosenTab) {
                $chosenTabType = 'routing';
                $relationField = null;
            } elseif (false === $objectView->hasViewTab($chosenTab)) {
                $chosenTab = null;
            }
        }

        if (null === $chosenTab) {
            $chosenTab = $objectView->getDefaultViewTab();
        }

        if (!isset($chosenTabType)) {
            list($chosenTabType, $relationField) = $objectView->getDefaultViewTabType($chosenTab);
        }

        // Remove useless objectviewFields
        if ('routing' != $chosenTab && null !== $objectView->getView()) {
            if ($objectView->getView()->hasChild($chosenTab)) {
                $parentNode = $objectView->getView()->getChild($chosenTab);
            }

            if (null === $chosenTab) {
                $parentNode = $objectView->getView();
            }

            $objectView->removeFields();
            foreach ($parentNode->getObjectViewFields() as $objectViewField) {
                $objectView->addField($objectViewField);
            }
        }

        return array($chosenTab, $chosenTabType, $objectView, $relationField, $hasRouting);
    }

    /*
     * Return Array
     */
    protected function getCreateParameters(Request $request, FormView $formView, ObjectDefinition $object)
    {
        // Remove useless formviewFields to avoid errors on form
        if (null !== ($parentNode = $formView->getView())) {
            $formView->removeFields();

            if ($formView->countTabs() > 0) {
                foreach ($parentNode->getChildren() as $childNode) {
                    if ($childNode->getChildType() == 'regularFields') {
                        foreach ($childNode->getFormViewFields() as $formViewField) {
                            $formView->addField($formViewField);
                        }
                    }
                }
            } else {
                foreach ($parentNode->getFormViewFields() as $formViewField) {
                    $formView->addField($formViewField);
                }
            }
        }

        return array($formView);
    }

    /*
     * Return Array
     */
    protected function getEditParameters(Request $request, FormView $formView, ObjectDefinition $object)
    {
        $hasRouting = $object->isSeoEnabled() && $this->container->get('security.context')->isGranted('ROLE_PA_ROUTING');

        if (null !== $chosenTab = $request->query->get('tab')) {
            if ($hasRouting && 'routing' == $chosenTab) {
                $chosenTabType = 'routing';
                $relationField = null;
            } elseif (false === $formView->hasViewTab($chosenTab)) {
                $chosenTab = null;
            }
        }

        if (null === $chosenTab) {
            $chosenTab = $formView->getDefaultViewTab();
        }

        if (!isset($chosenTabType)) {
            list($chosenTabType, $relationField) = $formView->getDefaultViewTabType($chosenTab);
        }

        // Remove useless formviewFields to avoid errors on form
        if ('routing' != $chosenTab && null !== $formView->getView()) {
            if ($formView->getView()->hasChild($chosenTab)) {
                $parentNode = $formView->getView()->getChild($chosenTab);
            }

            if (null === $chosenTab) {
                $parentNode = $formView->getView();
            }

            $formView->removeFields();
            foreach ($parentNode->getFormViewFields() as $formViewField) {
                $formView->addField($formViewField);
            }
        }

        return array($chosenTab, $chosenTabType, $formView, $relationField, $hasRouting);
    }
}

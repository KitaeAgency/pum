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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

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
        $object->raiseOnce(Events::OBJECT_DEFINITION_SEARCH_UPDATE, new ObjectDefinitionEvent($object));

        $this->get('pum')->saveBeam($beam);

        $this->addSuccess('Search index successfully reindexed');

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

        if ($request->isMethod('POST') && $form_filter->bind($request)->isSubmitted()) {
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

        $oem    = $this->get('pum.context')->getProjectOEM();
        $object = $oem->createObject($name);

        $formViewName = $request->query->get('view');
        if (empty($formViewName) || $formViewName === FormView::DEFAULT_NAME) {
            $formView = $objectDefinition->createDefaultFormView();
        } else {
            try {
                $formView = $objectDefinition->getFormView($formViewName);
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Form view not found.', $e);
            }
        }

        $form = $this->createForm('pum_object', $object, array(
            'form_view' => $formView
        ));

        if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
            return $response;
        }

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $oem->persist($object);
            $oem->flush();
            $this->addSuccess('Object successfully created');

            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $object->getId(), 'view' => $formView->getName())));
        }

        return $this->render('PumProjectAdminBundle:Object:create.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $beam->getObject($name),
            'form'              => $form->createView(),
            'object'            => $object,
        ));
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

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        $objectView = clone $object;

        $formViewName = $request->query->get('view');
        if ($formViewName === null || $formViewName === FormView::DEFAULT_NAME || $formViewName === '') {
            if ($formViewName === FormView::DEFAULT_NAME || null === $formView = $objectDefinition->getDefaultFormView()) {
                $formView = $objectDefinition->createDefaultFormView();
            }
        } else {
            try {
                $formView = $objectDefinition->getFormView($formViewName);
            } catch (DefinitionNotFoundException $e) {
                throw $this->createNotFoundException('Form view not found.', $e);
            }
        }

        $form = $this->createForm('pum_object', $object, array(
            'form_view' => $formView
        ));

        if ($response = $this->get('pum.form_ajax')->handleForm($form, $request)) {
            return $response;
        }

        if ($request->isMethod('POST') && $form->bind($request)->isValid()) {
            $oem->persist($object);
            $oem->flush();
            $this->addSuccess('Object successfully updated');

            return $this->redirect($this->generateUrl('pa_object_edit', array('beamName' => $beam->getName(), 'name' => $name, 'id' => $id, 'view' => $formView->getName())));
        }

        return $this->render('PumProjectAdminBundle:Object:edit.html.twig', array(
            'beam'              => $beam,
            'object_definition' => $objectDefinition,
            'form'              => $form->createView(),
            'object'            => $objectView,
        ));
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

        $oem = $this->get('pum.context')->getProjectOEM();
        $repository = $oem->getRepository($name);
        $this->throwNotFoundUnless($object = $repository->find($id));
        $objectView = clone $object;

        $formView = $beam->getObject($name)->createDefaultFormView();

        if ($request->isMethod('POST')) {
            $newObject = $oem->createObject($name);
            $form = $this->createForm('pum_object', $newObject, array(
                'form_view' => $formView
            ));
            if ($form->bind($request)->isValid()) {
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

        $query = array_merge($request->query->all(), array('filters' => $queryFilters));
        krsort($query);

        $url = $request->getBaseUrl().$request->getPathInfo().'?'.http_build_query($query);

        return $this->redirect($url);
    }

    /*
     * Return TableView
     * Get Default TableView
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
}

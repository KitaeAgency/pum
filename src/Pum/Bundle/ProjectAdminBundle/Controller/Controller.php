<?php

namespace Pum\Bundle\ProjectAdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Pum\Bundle\AppBundle\Controller\Controller as BaseController;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\View\TableView;
use Pum\Core\Definition\View\ObjectView;
use Pum\Core\Definition\View\FormView;
use Pum\Core\Exception\DefinitionNotFoundException;

abstract class Controller extends BaseController
{
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
            $tableView = $this->getDefaultTableView($this->getRequest()->query->get('view'), $object->getBeam(), $object);
            if (null !== $objectView = $tableView->getPreferredObjectView()) {
                return $objectView;
            }

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
}

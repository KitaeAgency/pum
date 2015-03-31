<?php

namespace Pum\Bundle\AppBundle\Extension\Permission\TableView;

use Pum\Core\ObjectFactory;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Core\Definition\View\TableView;
use Symfony\Component\HttpFoundation\Request;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomView;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomViewRepository;

/**
 * A TableViewSchema.
 */
class TableViewSchema
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var CustomViewRepository
     */
    protected $repository;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @var Beam
     */
    protected $beam;

    /**
     * @var ObjectDefinition
     */
    protected $objectDefinition;

    /**
     * @var array
     */
    protected $customViews;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @var array
     */
    protected $errors;

    public function __construct(ObjectFactory $objectFactory, CustomViewRepository $repository)
    {
        $this->objectFactory = $objectFactory;
        $this->repository = $repository;

        $this->group = null;
        $this->request = null;

        $this->errors = array();
        $this->customViews = null;
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    public function setBeam(Beam $beam)
    {
        $this->beam = $beam;

        return $this;
    }

    public function setObjectDefinition(ObjectDefinition $objectDefinition)
    {
        $this->objectDefinition = $objectDefinition;

        return $this;
    }

    public function getCustomViews()
    {
        if (!$this->customViews) {
            $this->customViews = $this->repository->findBy(array(
                'group'     => $this->group,
                'project'   => $this->project,
                'beam'      => $this->beam,
                'object'    => $this->objectDefinition
            ));
        }

        return $this->customViews;
    }

    public function createSchema()
    {
        if (null === $this->group) {
            throw new \RuntimeException('You need to define a group permission to create the TableViewSchema');
        }

        if (null === $this->project || null === $this->beam || null === $this->objectDefinition) {
            throw new \RuntimeException('Missing required parameters to create the TableViewSchema');
        }

        $this->schema = array(
            'tableviews' => array(),
            'attributes' => array(
                'default' => true
            )
        );

        foreach ($this->objectDefinition->getTableViews() as $tableView) {
            $this->schema['tableviews'][$tableView->getId()] = array(
                'id'        => $tableView->getId(),
                'name'      => $tableView->getName(),
                'tableView' => $tableView,
                'attribute' => $this->setAttributes($tableView)
            );
        }

        return $this;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    private function setAttributes(TableView $tableView)
    {
        $attributes = array(
            'view' => false,
            'default' => false
        );

        foreach ($this->getCustomViews() as $customView) {
            if ($customView->getTableView() && $customView->getTableView()->getId() == $tableView->getId()) {
                $attributes['view'] = true;

                if ($customView->getDefault()) {
                    $attributes['default'] = true;
                    $this->schema['attributes']['default'] = false;
                }
            }
        }

        return $attributes;
    }

    public function handleRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function isValid()
    {
        if (null === $this->request) {
            return false;
        }

        $isValid    = true;
        $data       = $this->request->request->get('tableviews');

        if (null === $data) {
            return false;
        }

        $this->errors = array();

        if (isset($data['tableview']) && is_array($data['tableview'])) {
            foreach ($data['tableview'] as $tableViewId => $tableView) {
                if (!isset($this->schema['tableviews'][$tableViewId])) {
                    $this->errors[] = sprintf('TableView with ID #%s does not exist.', $tableViewId);
                    $isValid = false;
                }
            }
        }

        return $isValid;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function saveSchema()
    {
        if (null === $this->request) {
            throw new \RuntimeException('Form is not submitted');
        }

        $data           = $this->request->request->get('tableviews');

        if (isset($data['tableview']) && is_array($data['tableview'])) {
            $tableViewsId   = array_keys($data['tableview']);

            foreach ($this->getCustomViews() as $customView) {
                if (!in_array($customView->getTableView()->getId(), $tableViewsId)) {
                    $this->repository->delete($customView);
                }
            }

            foreach ($data['tableview'] as $tableViewId => $tableView) {
                $customView = $this->repository->findOneBy(array(
                    'group' => $this->group,
                    'project' => $this->project,
                    'beam' => $this->beam,
                    'object' => $this->objectDefinition,
                    'tableView' => $tableViewId
                ));

                if (!$customView) {
                    $customView = new CustomView();
                    $customView->setGroup($this->group);
                    $customView->setProject($this->project);
                    $customView->setBeam($this->beam);
                    $customView->setObject($this->objectDefinition);
                    $customView->setTableView($this->schema['tableviews'][$tableViewId]['tableView']);
                }

                $customView->setDefault(false);
                if (isset($data['attribute']['default']) &&
                    $data['attribute']['default'] == $tableViewId) {
                    $customView->setDefault(true);
                }
                
                if (isset($tableView['attribute']['view']) && $tableView['attribute']['view']) {
                    $this->repository->save($customView);
                } else {
                    $this->repository->delete($customView);
                }
            }
        } else {
            $customViews = $this->getCustomViews();

            foreach ($customViews as $customView) {
                $this->repository->delete($customView);
            }
        }

        $this->customViews = null;
        $this->createSchema();
    }
}

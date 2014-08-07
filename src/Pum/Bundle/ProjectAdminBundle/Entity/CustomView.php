<?php

namespace Pum\Bundle\ProjectAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\View\TableView;
use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Bundle\AppBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="CustomViewRepository")
 * @ORM\Table(name="pa_custom_view")
 */
class CustomView
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Pum\Bundle\AppBundle\Entity\Group", inversedBy="customsViews")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $group;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Pum\Bundle\AppBundle\Entity\User", inversedBy="customsViews")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $user;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $project;

    /**
     * @var Beam
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\Beam")
     * @ORM\JoinColumn(name="beam_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $beam;

    /**
     * @var ObjectDefinition
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\ObjectDefinition")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $object;

    /**
     * @var TableView
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\View\TableView")
     * @ORM\JoinColumn(name="table_view_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $tableView;


    public function __construct(Project $project = null, Beam $beam = null, ObjectDefinition $object = null, TableView $tableView = null, Group $group = null, User $user = null)
    {
        $this->setProject($project);
        $this->setBeam($beam);
        $this->setObject($object);
        $this->setTableView($tableView);
        $this->setGroup($group);
        $this->setUser($user);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Project $project
     * @return $this
     */
    public function setProject(Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @param Beam $beam
     * @return $this
     */
    public function setBeam(Beam $beam = null)
    {
        $this->beam = $beam;

        return $this;
    }

    /**
     * @return Beam
     */
    public function getBeam()
    {
        return $this->beam;
    }

    /**
     * @param Group $group
     * @return $this
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param TableView $tableview
     * @return $this
     */
    public function setTableView(TableView $tableView = null)
    {
        $this->tableView = $tableView;

        return $this;
    }

    /**
     * @return TableView
     */
    public function getTableView()
    {
        return $this->tableView;
    }

    /**
     * @param ObjectDefinition $object
     * @return $this
     */
    public function setObject(ObjectDefinition $object = null)
    {
        $this->object = $object;

        return $this;
    }

    /**
     * @return ObjectDefinition
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @return string
     */
    public function getProjectName()
    {
        return null == $this->project ? null : $this->project->getName();
    }

    /**
     * @return string
     */
    public function getBeamName()
    {
        return null == $this->beam ? null : $this->beam->getName();
    }

    /**
     * @return string
     */
    public function getObjectName()
    {
        return null == $this->object ? null : $this->object->getName();
    }
}

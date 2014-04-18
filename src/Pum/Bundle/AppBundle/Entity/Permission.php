<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

/**
 * @ORM\Entity(repositoryClass="PermissionRepository")
 * @ORM\Table(name="ww_permission")
 */
class Permission
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
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="permissions")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    protected $group;

    /**
     * @var String
     *
     * @ORM\Column(name="keywords", type="string", length=255)
     */
    protected $attribute;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\Project")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true)
     */
    protected $project;

    /**
     * @var Beam
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\Beam")
     * @ORM\JoinColumn(name="beam_id", referencedColumnName="id", nullable=true)
     */
    protected $beam;

    /**
     * @var ObjectDefinition
     *
     * @ORM\ManyToOne(targetEntity="Pum\Core\Definition\ObjectDefinition")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true)
     */
    protected $object;

    /**
     * @var int
     *
     * @ORM\Column(name="instance_id", type="integer", nullable=true)
     */
    protected $instance;

    /**
     * @param String $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return String
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param Beam $beam
     * @return $this
     */
    public function setBeam(Beam $beam)
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
    public function setGroup(Group $group)
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $instance
     * @return $this
     */
    public function setInstance($instance)
    {
        $this->instance = $instance;

        return $this;
    }

    /**
     * @return int
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @param ObjectDefinition $object
     * @return $this
     */
    public function setObject(ObjectDefinition $object)
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
     * @param Project $project
     * @return $this
     */
    public function setProject(Project $project)
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
}

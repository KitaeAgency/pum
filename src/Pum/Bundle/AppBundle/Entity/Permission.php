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
    public static $objectPermissions = array(
        'PUM_OBJ_VIEW',
        'PUM_OBJ_EDIT',
        'PUM_OBJ_CREATE',
        'PUM_OBJ_DELETE',
        'PUM_OBJ_MASTER',
    );

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="advancedPermissions")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=false)
     */
    protected $group;

    /**
     * @var String
     *
     * @ORM\Column(name="attribute", type="string", length=255)
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
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", nullable=true)
     */
    protected $object;

    /**
     * @var int
     *
     * @ORM\Column(name="instance_id", type="integer", nullable=true)
     */
    protected $instance;

    public function __construct()
    {
        $this->beam = null;
        $this->object = null;
    }

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

    /**
     * @return string
     */
    public function getSubject()
    {
        if (null == $this->beam) {
            $subject = sprintf("All beams of %s", $this->project->getName());
        } else if (null == $this->object) {
            $subject = sprintf("All objects of %s / %s", $this->project->getName(), $this->beam->getName());
        } else if (null == $this->instance) {
            $subject = sprintf("All instances of %s / %s / %s", $this->project->getName(), $this->beam->getName(), $this->object->getName());
        }  else {
            $subject = sprintf("Unique instance: %s / %s / %s#%d", $this->project->getName(), $this->beam->getName(), $this->object->getName(), $this->instance);
        }

        return $subject;
    }
}

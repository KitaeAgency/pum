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
    public static $projectPermissions = array(
        'PUM_PROJECT_LIST',
        'PUM_PROJECT_CREATE',
        'PUM_PROJECT_VIEW',
        'PUM_PROJECT_EDIT',
        'PUM_PROJECT_DELETE',
    );

    public static $beamPermissions = array(
        'PUM_BEAM_LIST',
        'PUM_BEAM_CREATE',
        'PUM_BEAM_VIEW',
        'PUM_BEAM_EDIT',
        'PUM_BEAM_DELETE',
    );

    public static $objectPermissions = array(
        'PUM_OBJECT_LIST',
        'PUM_OBJECT_CREATE',
        'PUM_OBJECT_VIEW',
        'PUM_OBJECT_EDIT',
        'PUM_OBJECT_DELETE',
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

    /**
     * @return string
     */
    public function getSubject()
    {
        $subject = null;
        if (in_array($this->attribute, self::$projectPermissions)) {
            if (!$this->project) {
                $subject = 'All projects';
            } else {
                $subject = $this->project->getName();
            }
        } elseif (in_array($this->attribute, self::$beamPermissions)) {
            if (!$this->beam) {
                $subject = 'All beams';
            } else {
                $subject = $this->beam->getName();
            }
        }  elseif (in_array($this->attribute, self::$objectPermissions)) {
            if (!$this->object) {
                $subject = 'All objects';
            } else {
                $subject = $this->object->getName();
            }
        } else {
            //todo handle instances
        }

        return $subject;
    }
}

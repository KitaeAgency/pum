<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;

/**
 * @ORM\MappedSuperclass
 */
class Permission
{
    const PERMISSION_VIEW = 0b0001;
    const PERMISSION_CREATE = 0b010;
    const PERMISSION_EDIT = 0b0100;
    const PERMISSION_DELETE = 0b1000;

    public static $objectPermissions = array(
        'PUM_OBJ_VIEW' => self::PERMISSION_VIEW,
        'PUM_OBJ_EDIT' => self::PERMISSION_EDIT,
        'PUM_OBJ_CREATE' => self::PERMISSION_CREATE,
        'PUM_OBJ_DELETE' => self::PERMISSION_DELETE
    );

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

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
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param String $attribute
     * @return $this
     */
    private function setAttribute($attribute)
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

    public function getAttributes()
    {
        $attribute = $this->getAttribute();
        $attributes = array();

        foreach (self::$objectPermissions as $objectPermission => $mask) {
            if ($attribute & $mask) {
                $attributes[] = $objectPermission;
            }
        }

        return $attributes;
    }

    public function hasAttribute($permission)
    {
        if (array_key_exists($permission, self::$objectPermissions)) {
            $mask = self::$objectPermissions[$permission];

            return $this->getAttribute() & $mask;
        }

        return false;
    }

    public function setAttributes(array $permissions = array())
    {
        $this->setAttribute(null);

        foreach ($permissions as $permission) {
            $this->addAttribute($permission);
        }

        return $this;
    }

    public function addAttribute($permission)
    {
        $attribute = $this->getAttribute();

        if ($permission === 'PUM_OBJ_MASTER') {
            foreach (self::$objectPermissions as $mask) {
                $attribute |= $mask;
            }
        } else {
            if ($permission === 'PUM_OBJ_EDIT') {
                $attribute |= self::$objectPermissions['PUM_OBJ_VIEW'];
            }

            if (array_key_exists($permission, self::$objectPermissions)) {
                $attribute |= self::$objectPermissions[$permission];
            }
        }

        $this->setAttribute($attribute);

        return $this;
    }

    public function removeAttritebute($permission)
    {
        $attribute = $this->getAttribute();
        if (array_key_exists($permission, self::$objectPermission)) {
            $mask = self::$objectPermissions[$permission];

            $attribute &= ~$mask;
            $this->setAttribute($attribute);
        }

        return $this;
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
            $subject = sprintf("All objects of %s / %s", $this->project->getName(), $this->beam->getAliasName());
        } else if (null == $this->instance) {
            $subject = sprintf("All instances of %s / %s / %s", $this->project->getName(), $this->beam->getAliasName(), $this->object->getAliasName());
        } else {
            $subject = sprintf("Unique instance: %s / %s / %s#%d", $this->project->getName(), $this->beam->getAliasName(), $this->object->getAliasName(), $this->instance);
        }

        return $subject;
    }
}

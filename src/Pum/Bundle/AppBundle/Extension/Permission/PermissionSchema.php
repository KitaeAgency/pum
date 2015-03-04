<?php

namespace Pum\Bundle\AppBundle\Extension\Permission;

use Pum\Core\ObjectFactory;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\FieldDefinition;
use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;
use Pum\Bundle\AppBundle\Entity\PermissionRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 * A PermissionSchema.
 */
class PermissionSchema
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
     * @var PermissionRepository
     */
    protected $repository;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var array
     */
    protected $permissions;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @var array
     */
    protected $defaultAttributes;

    /**
     * @var array
     */
    protected $errors;

    /**
     * Constructor.
     */
    public function __construct(ObjectFactory $objectFactory, PermissionRepository $repository)
    {
        $this->objectFactory = $objectFactory;
        $this->repository    = $repository;

        $this->init();
    }

    public function init()
    {
        $this->group         = null;
        $this->request       = null;

        $this->schema        = array();
        $this->permissions   = array();
        $this->errors        = array();

        $this->defaultAttributes = array_flip(Permission::$objectPermissions);
        foreach ($this->defaultAttributes as $key => $attribute) {
            $this->defaultAttributes[$key] = null;
        }
    }

    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function handleRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    public function isValid()
    {
        if (null === $this->request) {
            throw new \RuntimeException('Form is not submitted');
        }

        // TODO check if form is valid


        return true;
    }

    public function createSchema()
    {
        if (null === $this->group) {
            throw new \RuntimeException('You need to define a group permission to create the PermissionSchema');
        }

        $this
            ->initPermissions()
            ->initSchema()
        ;

        return $this;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function saveSchema()
    {
        if (null === $this->request) {
            throw new \RuntimeException('Form is not submitted');
        }



        return $this;
    }

    private function initSchema()
    {
        $schema = array();

        foreach ($this->objectFactory->getAllProjects() as $project) {
            $schema[$project->getId()] = array(
                'id'        => $project->getId(),
                'name'      => $project->getName(),
                'attribute' => $this->setAttributes($project->getId()),
            );

            foreach ($project->getBeamsOrderBy('name') as $beam) {
                $schema[$project->getId()]['beams'][$beam->getId()] = array(
                    'id'        => $beam->getId(),
                    'name'      => $beam->getAliasName(),
                    'attribute' => $this->setAttributes($project->getId().$beam->getId()),
                );

                foreach ($beam->getObjectsOrderBy('name') as $object) {
                    $schema[$project->getId()]['beams'][$beam->getId()]['objects'][$object->getId()] = array(
                        'id'        => $object->getId(),
                        'name'      => $object->getAliasName(),
                        'attribute' => $this->setAttributes($project->getId().$beam->getId().$object->getId()),
                    );
                }
            }
        }

        $this->schema = $schema;

        return $this;
    }

    private function initPermissions()
    {
        $permissions      = array();
        $groupPermissions = $this->repository->getGroupPermissions($this->group, false);

        foreach ($groupPermissions as $permission) {
            $projectId = (null === $permission->getProject()) ? null : $permission->getProject()->getId();
            $beamId    = (null === $permission->getBeam())    ? null : $permission->getBeam()->getId();
            $objectId  = (null === $permission->getObject())  ? null : $permission->getObject()->getId();
            $key       = md5($projectId.$beamId.$objectId.$permission->getAttribute());
            $depth     = 1;

            if ($objectId) {
                $depth = 3;
            } elseif ($beamId) {
                $depth = 2;
            }

            $permissions[$key] = array(
                'id'        => $permission->getId(),
                'depth'     => $depth,
                'project'   => $projectId,
                'beam'      => $beamId,
                'object'    => $objectId,
                'attribute' => $permission->getAttribute()
            );
        }

        $this->permissions = $permissions;

        return $this;
    }

    private function setAttributes($key)
    {
        $attributes = $this->defaultAttributes;

        if (isset($this->permissions[$key])) {
            return array_merge($this->defaultAttributes, $this->permissions[$key]['attribute']);
        }

        foreach ($attributes as $k => $v) {
            $attributeKey = md5($key.$k);
            if (isset($this->permissions[$attributeKey])) {
                $attributes[$k] = 'checked="check"';
            }
        }

        return $attributes;
    }
}

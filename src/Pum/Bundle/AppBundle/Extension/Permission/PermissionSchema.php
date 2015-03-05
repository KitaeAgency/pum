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
    protected $hasPermissions;

    /**
     * @var array
     */
    protected $instancePermissionsCount;

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

        $this->schema                   = array();
        $this->permissions              = array();
        $this->hasPermissions           = array();
        $this->errors                   = array();
        $this->instancePermissionsCount = array();

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
        $isValid = true;
        $data    = $this->request->request->get('permission', array());

        foreach ($data as $projectId => $project) {
            if (!isset($this->schema[$projectId])) {
                $this->errors[] = sprintf('Project with ID #%s does not exist.', $projectId);
                $isValid        = false;
            }

            if (isset($project['beams'])) {
                foreach ($project['beams'] as $beamId => $beam) {
                    if (!isset($this->schema[$projectId]['beams'][$beamId])) {
                        $this->errors[] = sprintf('Beam with ID #%s does not exist.', $beamId);
                        $isValid        = false;
                    }

                    if (isset($beam['objects'])) {
                        foreach ($beam['objects'] as $objectId => $object) {
                            if (!isset($this->schema[$projectId]['beams'][$beamId]['objects'][$objectId])) {
                                $this->errors[] = sprintf('Object with ID #%s does not exist.', $objectId);
                                $isValid        = false;
                            }
                        }
                    }
                }
            }
        }

        return $isValid;
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

        $newPermissions = array();
        $data           = $this->request->request->get('permission', array());

        foreach ($data as $projectId => $project) {
            if (isset($project['activation']) && $project['activation']) {
                $masterProjectAttritubes = array();

                if (isset($project['attribute'])) {
                    if (isset($project['attribute']['PUM_OBJ_MASTER'])) {
                        $project['attribute']    = array('PUM_OBJ_MASTER' => '1');
                        $masterProjectAttritubes = Permission::$objectPermissions;

                        $this->repository->deleteSubPermissions($attribute = null, $this->group->getId(), $projectId);
                    }

                    foreach ($project['attribute'] as $key => $value) {
                        $masterProjectAttritubes[]            = $key;
                        $newPermissions[md5($projectId.$key)] = array(
                            'id'        => null,
                            'depth'     => 1,
                            'project'   => $projectId,
                            'beam'      => null,
                            'object'    => null,
                            'attribute' => $key,
                            'existed'   => (isset($this->permissions[md5($projectId.$key)])) ? true : false
                        );

                        $this->repository->deleteSubPermissions($attribute = $key, $this->group->getId(), $projectId);
                    }
                }

                if (isset($project['beams'])) {
                    foreach ($project['beams'] as $beamId => $beam) {
                        $masterBeamAttritubes = array();

                        if (isset($beam['attribute'])) {
                            if (isset($project['attribute']['PUM_OBJ_MASTER'])) {
                                $beam['attribute']    = array('PUM_OBJ_MASTER' => '1');
                                $masterBeamAttritubes = Permission::$objectPermissions;

                                $this->repository->deleteSubPermissions($attribute = null, $this->group->getId(), $projectId, $beamId);
                            }

                            foreach ($beam['attribute'] as $key => $value) {
                                if (!in_array($key, $masterProjectAttritubes)) {
                                    $masterBeamAttritubes[]                       = $key;
                                    $newPermissions[md5($projectId.$beamId.$key)] = array(
                                        'id'        => null,
                                        'depth'     => 2,
                                        'project'   => $projectId,
                                        'beam'      => $beamId,
                                        'object'    => null,
                                        'attribute' => $key,
                                        'existed'   => (isset($this->permissions[md5($projectId.$beamId.$key)])) ? true : false
                                    );

                                    $this->repository->deleteSubPermissions($attribute = $key, $this->group->getId(), $projectId, $beamId);
                                }
                            }
                        }

                        if (isset($beam['objects'])) {
                            foreach ($beam['objects'] as $objectId => $object) {
                                if (isset($object['attribute'])) {
                                    if (isset($object['attribute']['PUM_OBJ_MASTER'])) {
                                        $object['attribute'] = array('PUM_OBJ_MASTER' => '1');

                                        $this->repository->deleteSubPermissions($attribute = null, $this->group->getId(), $projectId, $beamId, $objectId);
                                    }

                                    foreach ($object['attribute'] as $key => $value) {
                                        if (!in_array($key, $masterProjectAttritubes) && !in_array($key, $masterBeamAttritubes)) {
                                            $newPermissions[md5($projectId.$beamId.$objectId.$key)] = array(
                                                'id'        => null,
                                                'depth'     => 3,
                                                'project'   => $projectId,
                                                'beam'      => $beamId,
                                                'object'    => $objectId,
                                                'attribute' => $key,
                                                'existed'   => (isset($this->permissions[md5($projectId.$beamId.$objectId.$key)])) ? true : false
                                            );

                                            $this->repository->deleteSubPermissions($attribute = $key, $this->group->getId(), $projectId, $beamId, $objectId);
                                        }
                                    }
                                }
                            }
                        }

                    }
                }

            } else {
                $this->repository->deletePermissions($attribute = null, $this->group->getId(), $projectId);
            }
        }

        foreach ($newPermissions as $newPermission) {
            if (false === $newPermission['existed']) {
                $this->repository->addPermission($newPermission['attribute'], $this->group->getId(), $newPermission['project'], $newPermission['beam'], $newPermission['object']);
            }
        }
        $this->repository->flush();

        return $this;
    }

    private function initSchema()
    {
        $schema               = array();
        $this->hasPermissions = array();

        foreach ($this->objectFactory->getAllProjects() as $project) {
            $schema[$project->getId()] = array(
                'id'             => $project->getId(),
                'name'           => $project->getName(),
                'hasPermissions' => null,
                'attribute'      => $this->setAttributes($project->getId(), $project->getId()),
                'beams'          => array()
            );

            foreach ($project->getBeamsOrderBy('name') as $beam) {
                $schema[$project->getId()]['beams'][$beam->getId()] = array(
                    'id'        => $beam->getId(),
                    'name'      => $beam->getAliasName(),
                    'attribute' => $this->setAttributes($project->getId().$beam->getId(), $project->getId()),
                    'objects'   => array()
                );

                foreach ($beam->getObjectsOrderBy('name') as $object) {
                    $schema[$project->getId()]['beams'][$beam->getId()]['objects'][$object->getId()] = array(
                        'id'             => $object->getId(),
                        'name'           => $object->getAliasName(),
                        'attribute'      => $this->setAttributes($project->getId().$beam->getId().$object->getId(), $project->getId()),
                        'subPermissions' => (isset($this->instancePermissionsCount[md5($project->getId().$beam->getId().$object->getId())])) ? $this->instancePermissionsCount[md5($project->getId().$beam->getId().$object->getId())] : 0
                    );
                }
            }
        }

        foreach ($this->hasPermissions as $id => $value) {
            $schema[$id]['hasPermissions'] = $value;
        }

        $this->schema = $schema;

        return $this;
    }

    private function initPermissions()
    {
        $permissions      = array();
        $groupPermissions = $this->repository->getGroupPermissions($this->group);

        foreach ($groupPermissions as $permission) {
            $projectId  = (null === $permission->getProject()) ? null : $permission->getProject()->getId();
            $beamId     = (null === $permission->getBeam())    ? null : $permission->getBeam()->getId();
            $objectId   = (null === $permission->getObject())  ? null : $permission->getObject()->getId();
            $instanceId = (!$permission->getInstance())        ? null : $permission->getInstance();
            $key        = md5($projectId.$beamId.$objectId.$instanceId.$permission->getAttribute());
            $depth      = 1;

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
                'instance'  => $instanceId,
                'attribute' => $permission->getAttribute()
            );

            if ($instanceId) {
                if (!isset($this->instancePermissionsCount[md5($projectId.$beamId.$objectId)])) {
                    $this->instancePermissionsCount[md5($projectId.$beamId.$objectId)] = 1;
                } else {
                    $this->instancePermissionsCount[md5($projectId.$beamId.$objectId)]++;
                }
            }
        }

        $this->permissions = $permissions;

        return $this;
    }

    private function setAttributes($key, $projectId)
    {
        $attributes = $this->defaultAttributes;

        if (isset($this->permissions[$key])) {
            return array_merge($this->defaultAttributes, $this->permissions[$key]['attribute']);
        }

        foreach ($attributes as $k => $v) {
            $attributeKey = md5($key.$k);

            if (isset($this->permissions[$attributeKey])) {
                $attributes[$k]                   = 'checked="check"';
                $this->hasPermissions[$projectId] = 'checked="check"';
            }
        }

        return $attributes;
    }
}

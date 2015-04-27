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
use Pum\Bundle\AppBundle\Entity\GroupPermissionRepository;
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
     * @var GroupPermissionRepository
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
    public function __construct(ObjectFactory $objectFactory, GroupPermissionRepository $repository)
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

        $this->defaultAttributes = Permission::$objectPermissions;
        foreach ($this->defaultAttributes as $key => $attribute) {
            $this->defaultAttributes[$key] = null;
        }
        $this->defaultAttributes['PUM_OBJ_MASTER'] = null;
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

                if (isset($project['attributes'])) {
                    if (isset($project['attributes']['PUM_OBJ_MASTER'])) {
                        $project['attributes']    = array('PUM_OBJ_MASTER' => '1');
                        $masterProjectAttritubes = array('PUM_OBJ_MASTER');
                    }

                    if (!empty($project['attributes'])) {
                        $update = true;
                        $attributes = array_keys($project['attributes']);

                        if (isset($this->permissions[md5($projectId)])) {
                            if ($this->permissions[md5($projectId)]['attributes'] === $attributes) {
                                $update = false;
                            }
                        }

                        $newPermissions[md5($projectId)] = array(
                            'id'            => null,
                            'depth'         => 1,
                            'project'       => $projectId,
                            'beam'          => null,
                            'object'        => null,
                            'attributes'    => $attributes,
                            'existed'       => !$update
                        );

                        $masterProjectAttritubes = $attributes;
                    }
                }

                if (isset($project['beams'])) {
                    foreach ($project['beams'] as $beamId => $beam) {
                        $masterBeamAttritubes = array();

                        if (isset($beam['attributes'])) {
                            if (isset($beam['attributes']['PUM_OBJ_MASTER'])) {
                                $beam['attributes']    = array('PUM_OBJ_MASTER' => '1');
                                $masterBeamAttritubes = array('PUM_OBJ_MASTER');
                            }

                            if (!empty($beam['attributes'])) {
                                $attributes = array_keys($beam['attributes']);
                                $projectDiff = array_diff($attributes, $masterProjectAttritubes);

                                if (!empty($projectDiff)) {
                                    $update = true;

                                    if (isset($this->permissions[md5($projectId.$beamId)])) {
                                        if ($this->permissions[md5($projectId.$beamId)]['attributes'] === $attributes) {
                                            $update = false;
                                        }
                                    }

                                    $newPermissions[md5($projectId.$beamId)] = array(
                                        'id'            => null,
                                        'depth'         => 2,
                                        'project'       => $projectId,
                                        'beam'          => $beamId,
                                        'object'        => null,
                                        'attributes'    => $attributes,
                                        'existed'       => !$update
                                    );

                                    $masterBeamAttritubes = $attributes;
                                }
                            }
                        }

                        if (isset($beam['objects'])) {
                            foreach ($beam['objects'] as $objectId => $object) {
                                if (isset($object['attributes'])) {
                                    if (isset($object['attributes']['PUM_OBJ_MASTER'])) {
                                        $object['attributes'] = array('PUM_OBJ_MASTER' => '1');
                                    }

                                    if (!empty($object['attributes'])) {
                                        $attributes = array_keys($object['attributes']);
                                        $beamDiff = array_diff($attributes, $masterBeamAttritubes);
                                        $projectDiff = array_diff($attributes, $masterProjectAttritubes);

                                        if (empty($beamDiff) || empty($projectDiff)) {
                                            continue;
                                        }

                                        $update = true;

                                        if (isset($this->permissions[md5($projectId.$beamId.$objectId)])) {
                                            if ($this->permissions[md5($projectId.$beamId.$objectId)]['attributes'] === $attributes) {
                                                $update = false;
                                            }
                                        }

                                        $newPermissions[md5($projectId.$beamId.$objectId)] = array(
                                            'id'            => null,
                                            'depth'         => 3,
                                            'project'       => $projectId,
                                            'beam'          => $beamId,
                                            'object'        => $objectId,
                                            'attributes'    => $attributes,
                                            'existed'       => !$update
                                        );
                                    }
                                }
                            }
                        }

                    }
                }

            } else {
                $this->repository->deletePermissions($this->group->getId(), $projectId);
            }
        }

        $toDelete = array();
        foreach ($this->permissions as $key => $value) {
            if (!$value['instance'] && !isset($newPermissions[$key])) {
                $toDelete[] = $value['id'];
            }
        }
        $this->repository->deleteByIds($toDelete);

        foreach ($newPermissions as $newPermission) {
            if (false === $newPermission['existed']) {
                $this->repository->addPermission($newPermission['attributes'], $this->group->getId(), $newPermission['project'], $newPermission['beam'], $newPermission['object']);
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
                'attributes'      => $this->setAttributes($project->getId(), $project->getId()),
                'beams'          => array()
            );

            foreach ($project->getBeamsOrderBy('name') as $beam) {
                $schema[$project->getId()]['beams'][$beam->getId()] = array(
                    'id'        => $beam->getId(),
                    'name'      => $beam->getName(),
                    'alias'     => $beam->getAliasName(),
                    'icon'      => $beam->getIcon(),
                    'attributes' => $this->setAttributes($project->getId().$beam->getId(), $project->getId()),
                    'objects'   => array()
                );

                foreach ($beam->getObjectsOrderBy('name') as $object) {
                    $schema[$project->getId()]['beams'][$beam->getId()]['objects'][$object->getId()] = array(
                        'id'             => $object->getId(),
                        'name'           => $object->getName(),
                        'alias'          => $object->getAliasName(),
                        'attributes'      => $this->setAttributes($project->getId().$beam->getId().$object->getId(), $project->getId()),
                        'hasTableViews'  => $object->getTableViews()->count() > 0 ? true : false,
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
            $depth      = 1;

            if ($objectId) {
                $depth = 3;
            } elseif ($beamId) {
                $depth = 2;
            }

            $permissions[md5($projectId.$beamId.$objectId.$instanceId)] = array(
                'id'        => $permission->getId(),
                'depth'     => $depth,
                'project'   => $projectId,
                'beam'      => $beamId,
                'object'    => $objectId,
                'instance'  => $instanceId,
                'attributes' => $permission->getAttributes()
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
            return array_merge($this->defaultAttributes, $this->permissions[$key]['attributes']);
        }

        $attributeKey = md5($key);
        if (isset($this->permissions[$attributeKey])) {
            $this->hasPermissions[$projectId] = 'checked="check"';

            foreach ($attributes as $k => $v) {
                if (isset($this->permissions[$attributeKey]['attributes']) && in_array($k, $this->permissions[$attributeKey]['attributes'])) {
                    $attributes[$k] = 'checked="check"';
                }
            }
        }

        return $attributes;
    }
}

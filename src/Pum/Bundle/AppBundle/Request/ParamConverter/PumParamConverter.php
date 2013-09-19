<?php

namespace Pum\Bundle\AppBundle\Request\ParamConverter;

use Pum\Core\ObjectFactory;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Definition\Relation;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Convert Pum Object instances from request attribute variable.
 */
class PumParamConverter implements ParamConverterInterface
{
    protected $objectFactory;
    protected $schemaObjects;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
        $this->schemaObjects = array(
            'Beam'             => 'beamName',
            'ObjectDefinition' => 'objectDefinitionName',
            'Project'          => 'projectName',
            'Relation'         => 'relationId'
        );
    }

    /**
     * @{inheritdoc}
     * 
     * @throws NotFoundHttpException When invalid object given
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $object       = null;
        $name         = $configuration->getName();
        $class        = $configuration->getClass();
        $options      = $configuration->getOptions();
        $mappingField = (isset($options[$this->schemaObjects[$class]])) ? $options[$this->schemaObjects[$class]] : $this->schemaObjects[$class];

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }

        switch ($class) {
            case "Beam":
                if ($request->attributes->has($mappingField)) {
                    $object = $this->objectFactory->getBeam($request->attributes->get($mappingField));
                }
                break;
            case "ObjectDefinition":
                if ($request->attributes->has('beam')) {
                    if ($request->attributes->has($mappingField)) {
                        $object = $request->attributes->get('beam')->getObject($request->attributes->get($mappingField));
                    }
                }
                break;
            case "Project":
                if ($request->attributes->has($mappingField)) {
                    $object = $this->objectFactory->getProject($request->attributes->get($mappingField));
                }
                break;
            case "Relation":
                if ($request->attributes->has('beam')) {
                    if ($request->attributes->has($mappingField)) {
                        $object = $request->attributes->get('beam')->getRelation($request->attributes->get($mappingField));
                    }
                }
                break;
        }

        if (null === $object && false === $configuration->isOptional()) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }

        $request->attributes->set($name, $object);

        return true;
    }

    /**
     * @{inheritdoc}
     */
    public function supports(ConfigurationInterface $configuration)
    {
        if (null === $configuration->getClass()) {
            return false;
        }

        if (isset($this->schemaObjects[$configuration->getClass()])) {
            return true;
        } else {
            return false;
        }
    }
}

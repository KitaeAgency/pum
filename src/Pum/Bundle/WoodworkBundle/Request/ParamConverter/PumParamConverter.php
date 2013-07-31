<?php

namespace Pum\Bundle\WoodworkBundle\Request\ParamConverter;

use Pum\Core\SchemaManager;
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
 *
 */
class PumParamConverter implements ParamConverterInterface
{
    /**
     * @var PumManager
     */
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * @{inheritdoc}
     * 
     * @throws NotFoundHttpException When invalid object given
     */
    public function apply(Request $request, ConfigurationInterface $configuration)
    {
        $object  = null;
        $name    = $configuration->getName();
        $class   = $configuration->getClass();
        $options = $configuration->getOptions();

        if (null === $request->attributes->get($name, false)) {
            $configuration->setIsOptional(true);
        }

        switch ($class) {
            case "Beam":
                $mappingField = (isset($options['beamName'])) ? $options['beamName'] : 'beamName';
                if ($request->attributes->has($mappingField)) {
                    $object = $this->schemaManager->getBeam($request->attributes->get($mappingField));
                }
                break;
            case "ObjectDefinition":
                if ($request->attributes->has('beam')) {
                    $mappingField = (isset($options['objectDefinitionName'])) ? $options['objectDefinitionName'] : 'objectDefinitionName';
                    if ($request->attributes->has($mappingField)) {
                        $object = $request->attributes->get('beam')->getObject($request->attributes->get($mappingField));
                    }
                }
                break;
            case "Project":
                $mappingField = (isset($options['projectName'])) ? $options['projectName'] : 'projectName';
                if ($request->attributes->has($mappingField)) {
                    $object = $this->schemaManager->getProject($request->attributes->get($mappingField));
                }
                break;
            case "Relation":
                if ($request->attributes->has('beam')) {
                    $mappingField = (isset($options['relationId'])) ? $options['relationId'] : 'relationId';
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

        if ($configuration->getClass() === "Beam" ||
            $configuration->getClass() === "ObjectDefinition" ||
            $configuration->getClass() === "Project" ||
            $configuration->getClass() === "Relation") {
                return true;
        } else {
            return false;
        }
    }
}

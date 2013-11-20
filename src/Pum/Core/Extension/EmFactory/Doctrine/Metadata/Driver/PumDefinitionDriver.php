<?php

namespace Pum\Core\Extension\EmFactory\Doctrine\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Pum\Core\Context\FieldContext;
use Pum\Core\Exception\TypeNotFoundException;
use Pum\Core\Extension\EmFactory\EmFactoryFeatureInterface;
use Pum\Core\ObjectFactory;
use Pum\Core\SchemaManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PumDefinitionDriver implements MappingDriver
{
    protected $factory;

    public function __construct(ObjectFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        if (!$this->factory->isProjectClass($className)) {
            throw new \RuntimeException(sprintf('Class "%s" is not a project class, cannot load metadatas.', $className));
        }

        list($project, $object) = $this->factory->getProjectAndObjectFromClass($className);

        // An ID for all
        $metadata->mapField(array(
            'fieldName' => 'id',
            'type'      => 'integer',
        ));
        $metadata->setIdentifier(array('id'));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_AUTO);

        // Tablename
        $metadata->setTableName($x = 'obj__'.$project->getLowercaseName().'__'.$object->getLowercaseName());
        $repositoryClass = (null !== $object->getRepositoryClass()) ? $object->getRepositoryClass() : 'Pum\Core\Object\ObjectRepository';
        $metadata->setCustomRepositoryClass($repositoryClass);

        foreach ($object->getFields() as $field) {
            try {
                $types = $this->factory->getTypeHierarchy($field->getType());
            } catch (TypeNotFoundException $e) {
                $project->addContextError(sprintf(
                    'Field "%s": type "%s" does not exist.',
                    $object->getName().'::'.$field->getName(),
                    $field->getType()
                ));

                continue;
            }
            $options = $field->getTypeOptions();

            $resolver = new OptionsResolver();
            foreach ($types as $type) {
                $type->setDefaultOptions($resolver);
            }
            $options = $resolver->resolve($options);

            $context = new FieldContext($project, $field, $options);
            $context->setObjectFactory($this->factory);

            foreach ($types as $type) {
                if ($type instanceof EmFactoryFeatureInterface) {
                    $type->mapDoctrineField($context, $metadata);
                }
            }
        }

        // @todo behaviors
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        throw new \RuntimeException('This operation is not implemented');
    }

    /**
     * {@inheritdoc}
     */
    public function isTransient($className)
    {
        return true;
    }
}

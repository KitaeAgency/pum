<?php

namespace Pum\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\AbstractType;
use Pum\Core\ClassBuilder\Property;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\FieldDefinition;
use Pum\Extension\EmFactory\EmFactoryFeatureInterface;
use Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class SimpleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'simple';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required' => false,
            'unique'   => true,

            // em factory options
            'emf_type'      => 'text',
            'emf_length'    => null,
            'emf_precision' => null,
            'emf_scale'     => null,
            'emf_required'  => function (Options $options) {
                return $options['required'];
            },
            'emf_unique'    => function (Options $options) {
                return $options['unique'];
            },

            // project admin options
            'pa_form_type'              => 'text',
            'pa_form_options'           => array('required' => false),
            'pa_validation_constraints' => function (Options $options) {
                if ($options['required']) {
                    return array(new NotBlank());
                }

                return array();
            },
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $camelCase = $context->getField()->getCamelCaseName();
        $cb = $context->getClassBuilder();

        $cb
            ->createProperty($camelCase)
            ->addGetMethod($camelCase)
            ->addSetMethod($camelCase)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, DoctrineMetadata $metadata)
    {
        $metadata->mapField(array(
            'fieldName'  => $context->getField()->getCamelCaseName(),
            'columnName' => $context->getField()->getLowercaseName(),
            'type'       => $context->getOption('emf_type'),
            'unique'     => $context->getOption('emf_unique'),
            'length'     => $context->getOption('emf_length'),
            'precision'  => $context->getOption('emf_precision'),
            'scale'      => $context->getOption('emf_scale'),
        ));
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $by = $qb->getRootAlias() . '.' . $context->getField()->getCamelCaseName();
        $qb->orderby($by, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        foreach ($options['validation_constraints'] as $constraint) {
            $metadata->addGetterConstraint($name, $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormBuilderInterface $builder)
    {
        $builder->add($field->getLowercaseName(), $options['pa_form_type'], $options['pa_form_options']);
    }
}

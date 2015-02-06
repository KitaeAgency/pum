<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pum\Core\Relation\Relation;
use Pum\Core\Extension\Core\DataTransformer\PumEntityToValueTransformer;
use Pum\Core\Extension\Core\DataTransformer\PumEntitiesToValueTransformer;

class RelationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'target_beam'           => null,
                'target'                => null,
                'target_beam_seed'      => null,
                'inversed_by'           => null,
                'index_by'              => null,
                'cascade'               => null,
                'type'                  => null,
                'is_external'           => null,
                'required'              => false,
                'owning'                => true,
                'is_sleeping'           => false
            )
        );
    }

    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        // We do not edit relation there anymore, use schema class instead
        //$types = array_combine(Relation::getTypes(), Relation::getTypes());

        $builder
            ->add($builder->create('relations', 'alert', array(
                'attr' => array(
                    'class' => 'alert-warning text-center'
                ),
                'label' => 'pum.form.alert.object.relations'
            )))
            ->add('target_beam', 'hidden')
            ->add('target_beam_seed', 'hidden')
            ->add('target', 'hidden')
            ->add('inversed_by', 'hidden')
            ->add('index_by', 'hidden')
            ->add('cascade', 'hidden')
            ->add('type', 'hidden')
            ->add('is_external', 'hidden')
            ->add('is_sleeping', 'hidden')
            ->add('owning', 'hidden')
            ->add('required', 'hidden')
        ;
    }

    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
    {
        $forceType = $formViewField->getOption('force_type', 'pum_object_entity');
        $formType  = $formViewField->getOption('form_type', 'search');

        switch ($formType) {
            case 'tab':
                // Relation limit => Use Add/Remove method instead of Collections
                break;

            case 'collection':
                $forceType = $formViewField->getOption('force_type', 'pum_object');

                $form->add($context->getField()->getCamelCaseName(), 'collection', array(
                    'type'           => $forceType,
                    'allow_add'      => $formViewField->getOption('allow_add', true),
                    'allow_delete'   => $formViewField->getOption('allow_delete', true),
                    'prototype'      => $formViewField->getOption('prototype', true),
                    'prototype_name' => $formViewField->getOption('prototype_name', '__name__'),
                    'mapped'         => $formViewField->getOption('mapped', true),
                    'label'          => $formViewField->getLabel(),
                    'required'       => $context->getOption('required'),
                    'by_reference'  => !(in_array($context->getOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY))),
                    'options'       => array(
                        'pum_object'      => $context->getOption('target'),
                        'form_view'       => $formViewField->getOption('form_view', null),
                        'with_submit'     => $formViewField->getOption('with_submit', false),
                        'dispatch_events' => $formViewField->getOption('dispatch_events', false),
                    )
                ));
                break;

            case 'search':
                $form->add($context->getField()->getCamelCaseName(),'pum_ajax_object_entity', array(
                    'pum_object'    => $context->getOption('target'),
                    'target'        => $context->getOption('target'),
                    'field_name'    => $context->getField()->getCamelCaseName(),
                    'property_name' => $formViewField->getOption('property', 'id'),
                    'ids_delimiter' => $formViewField->getOption('delimiter', '-'),
                    'multiple'      => in_array($context->getOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY)),
                    'label'         => $formViewField->getLabel(),
                    'required'      => $context->getOption('required')
                ));

                // Reset viewTransformer to remove default ChoiceToValueTransformer or ChoicesToValueTransformer
                $form->get($context->getField()->getCamelCaseName())->resetViewTransformers();
                if (in_array($context->getOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY))) {
                    $form->get($context->getField()->getCamelCaseName())->addViewTransformer(new PumEntitiesToValueTransformer());
                }
                else {
                    $form->get($context->getField()->getCamelCaseName())->addViewTransformer(new PumEntityToValueTransformer());
                }
                break;

            default: 
                $form->add($context->getField()->getCamelCaseName(), $forceType, array(
                    'pum_object'   => $context->getOption('target'),
                    'multiple'     => in_array($context->getOption('type'), array(Relation::ONE_TO_MANY, Relation::MANY_TO_MANY)),
                    'label'        => $formViewField->getLabel(),
                    'ajax'         => $formType == 'ajax',
                    'required'     => $context->getOption('required')
                ));
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormViewOptions(FormBuilderInterface $builder, FormViewField $formViewField)
    {
        $beamName   = $formViewField->getField()->getTypeOption('target_beam');
        $beamSeed   = $formViewField->getField()->getTypeOption('target_beam_seed');
        $objectName = $formViewField->getField()->getTypeOption('target');
        $choices    = array();

        if (null !== $beamName && null !== $objectName && null !== $beamSeed) {
            foreach ($formViewField->getField()->getObject()->getBeam()->getProjects() as $project) {
                foreach ($project->getBeams() as $_beam) {
                    if ($_beam->getName() == $beamName && $_beam->getSeed() == $beamSeed) {
                        $beam = $_beam;

                        break;
                    }
                }

                if (isset($beam)) {
                    break;
                }
            }

            if (isset($beam) && $beam->hasObject($objectName)) {
                $object = $beam->getObject($objectName);
                $choices[] = 'id';

                foreach ($object->getFields() as $field) {
                    if ($field->getType() == 'text') {
                        $choices[]= $field->getCamelCaseName();
                    }
                }
            }
        }

        $builder
            ->add('form_type', 'choice', array(
                'choices'   =>  array(
                    'search'  => 'pa.form.formview.fields.entry.options.form.type.types.search'/*'Ajax Search list'*/,
                    'tab'     => 'pa.form.formview.fields.entry.options.form.type.types.tab'/*'Add/Remove method'*/,
                    'static'  => 'pa.form.formview.fields.entry.options.form.type.types.static'/*'Regular select list'*/,
                    //'ajax'    => 'pa.form.formview.fields.entry.options.form.type.types.ajax'/*'Ajax list'*/,
                )
            ))
            ->add('property', 'choice', array(
                'required'    =>  false,
                'empty_value' => false,
                'choices'     => array_combine($choices, $choices)
            ))
            ->add('allow_add', 'checkbox', array(
                'required'  =>  false
            ))
            ->add('allow_select', 'checkbox', array(
                'required'  =>  false
            ))
        ;
    }

    public function buildField(FieldBuildContext $context)
    {
        $cb       = $context->getClassBuilder();
        $camel    = $context->getField()->getCamelCaseName();
        $factory  = $context->getObjectFactory();
        $target   = $context->getOption('target');
        $isOwning = $context->getOption('owning');

        try {
            $class = $factory->getClassName($context->getProject()->getName(), $target);
        } catch (DefinitionNotFoundException $e) {
            $context->addError(sprintf(
                'Field "%s": target entity "%s" does not exist in project "%s".',
                $context->getObject()->getName().'::'.$camel,
                $target,
                $context->getProject()->getName()
            ));

            return;
        }

        $inverseField = null;
        if ($context->getOption('inversed_by')) {
            try {
                $inverseField = $context->getProject()->getObject($target)->getField($context->getOption('inversed_by'))->getCamelCaseName();
                $singularInverseField = Namer::getSingular($inverseField);
            } catch (DefinitionNotFoundException $e) {
                $context->addError('Inverse field not found on "%s:%s".', $target, $context->getOption('inversed_by'));
            }
        }

        $cb->createProperty($camel);

        $cb->createMethod('get'.ucfirst($camel), '', '
            return $this->'.$camel.';
        ');

        $type = $context->getOption('type');

        if ($type == Relation::ONE_TO_MANY || $type == Relation::MANY_TO_MANY) {
            $singular = Namer::getSingular($camel);

            $cb->prependOrCreateMethod('__construct', '', '
                $this->'.$camel.' = new \Doctrine\Common\Collections\ArrayCollection();
            ');

            // ADD METHOD
            if ($type == Relation::MANY_TO_MANY) {
                // Owning side relation so we don't need to set reverse relation (cf Doctrine)
                if ($isOwning) {
                    $cb->createMethod('add'.ucfirst($singular), $class.' $'.$singular, '
                        if (!$this->get'.ucfirst($camel).'()->contains($'.$singular.')) {
                            $this->get'.ucfirst($camel).'()->add($'.$singular.');
                        }

                        return $this;
                    ');
                } else {
                    $cb->createMethod('add'.ucfirst($singular), $class.' $'.$singular, '
                        if (!$this->get'.ucfirst($camel).'()->contains($'.$singular.')) {
                            $this->get'.ucfirst($camel).'()->add($'.$singular.');
                            '.($inverseField ? '$'.$singular.'->add'.ucfirst($singularInverseField).'($this);' : '').'
                        }

                        return $this;
                    ');
                }

            } else {
                $cb->createMethod('add'.ucfirst($singular), $class.' $'.$singular, '
                    if (!$this->get'.ucfirst($camel).'()->contains($'.$singular.')) {
                        $this->get'.ucfirst($camel).'()->add($'.$singular.');
                        '.($inverseField ? '$'.$singular.'->set'.ucfirst($singularInverseField).'($this);' : '').'
                    }

                    return $this;
                ');
            }

            // REMOVE METHOD
            if ($type == Relation::MANY_TO_MANY) {
                // Owning side relation so we don't need to set reverse relation (cf Doctrine)
                if ($isOwning) {
                    $cb->createMethod('remove'.ucfirst($singular), $class.' $'.$singular, '
                        if ($this->get'.ucfirst($camel).'()->contains($'.$singular.')) {
                            $this->get'.ucfirst($camel).'()->removeElement($'.$singular.');
                        }

                        return $this;
                    ');
                } else {
                    $cb->createMethod('remove'.ucfirst($singular), $class.' $'.$singular, '
                        if ($this->get'.ucfirst($camel).'()->contains($'.$singular.')) {
                            $this->get'.ucfirst($camel).'()->removeElement($'.$singular.');
                            '.($inverseField ? '$'.$singular.'->remove'.ucfirst($singularInverseField).'($this);' : '').'
                        }

                        return $this;
                    ');
                }

            } else {
                $cb->createMethod('remove'.ucfirst($singular), $class.' $'.$singular, '
                    if ($this->get'.ucfirst($camel).'()->contains($'.$singular.')) {
                        $this->get'.ucfirst($camel).'()->removeElement($'.$singular.');
                        '.($inverseField ? '$'.$singular.'->set'.ucfirst($singularInverseField).'(null);' : '').'
                    }

                    return $this;
                ');
            }

            if ($type == Relation::ONE_TO_MANY) {
                $cb->createMethod('get'.ucfirst($camel).'By', 'array $criterias, array $orderBy = null, $limite = null, $offset = null', '
                    $criteria = \Doctrine\Common\Collections\Criteria::create();
                    if (count($criterias, COUNT_RECURSIVE) === 1) {
                        $criterias = array($criterias);
                    }
                    foreach ($criterias as $where) {
                        foreach ($where as $key => $data) {
                            $data     = (array)$data;
                            $value    = (isset($data[0])) ? $data[0] : null;
                            $operator = (isset($data[1])) ? $data[1] : "eq";
                            $method   = (isset($data[2])) ? $data[2] : "andWhere";
                            if (!in_array($method, array("andWhere", "orWhere"))) {
                                $method = "andWhere";
                            }
                            if (!in_array($operator, array("andX", "orX", "eq", "gt", "lt", "lte", "gte", "neq", "isNull", "in", "notIn"))) {
                                $operator = "eq";
                            }
                            $criteria->$method(\Doctrine\Common\Collections\Criteria::expr()->$operator($key, $value));
                        }
                    }
                    if (null !== $limite) {
                        $criteria->setMaxResults($limite);
                    }
                    if (null !== $offset) {
                        $criteria->setFirstResult($offset);
                    }
                    if (null !== $orderBy) {
                        $criteria->orderBy($orderBy);
                    }

                    return $this->'.$camel.'->matching($criteria);
                ');
            }


        } else {
            // One-to-one reverse setMethod (cf Owning side relation)
            if ($type == Relation::ONE_TO_ONE && null !== $inverseField && false === $isOwning) {
                $cb->createMethod('set'.ucfirst($camel), $class.' $'.$camel.' = null', '
                    if ($'.$camel.' !== null) {
                        $this->'.$camel.' = $'.$camel.';
                        $'.$camel.'->set'.ucfirst($inverseField).'($this);
                    } elseif ($this->'.$camel.' !== null) {
                        $this->'.$camel.'->set'.ucfirst($inverseField).'(null);
                    }

                    return $this;
                ');

            } else {
                $cb->createMethod('set'.ucfirst($camel), $class.' $'.$camel.' = null', '
                    $this->'.$camel.' = $'.$camel.';

                    return $this;
                ');
            }
        }
    }

    public function mapDoctrineField(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $camel    = $context->getField()->getCamelCaseName();
        $factory  = $context->getObjectFactory();
        $source   = $context->getField()->getObject()->getName();
        $target   = $context->getOption('target');
        $isOwning = $context->getOption('owning');

        try {
            $targetClass = $factory->getClassName($context->getProject()->getName(), $target);
        } catch (DefinitionNotFoundException $e) {
            $context->addError(sprintf(
                'Field "%s": target entity "%s" does not exist in project "%s"',
                $context->getObject()->getName().'::'.$camel,
                $target,
                $context->getProject()->getName()
            ));

            return;
        }

        $inversedBy = $context->getOption('inversed_by');
        if ($inversedBy) {
            try {
                $inversedField = $context->getProject()->getObject($target)->getField($inversedBy);
                $inversedBy    = Namer::toCamelCase($inversedBy);
            } catch (DefinitionNotFoundException $e) {
                $context->addError(sprintf(
                    'Field "%s": target entity "%s" does not have field "%s".',
                    $context->getObject()->getName().'::'.$camel,
                    $target,
                    $inversedBy
                ));

                $inversedBy = null;
            }
        }

        $indexBy = $context->getOption('index_by');
        $cascade = array();
        if (!empty($context->getOption('cascade'))) {
            $cascade = explode(',', $context->getOption('cascade'));
        }

        $type = $context->getOption('type');
        $joinTable = 'obj__'.$context->getProject()->getLowercaseName().'__assoc__'.$context->getField()->getObject()->getLowercaseName().'__'.$context->getField()->getLowercaseName();

        switch ($type) {
            case Relation::MANY_TO_MANY:
                # Self relation case
                if ($source == $target) {
                    $source = 'left_'.$source;
                    $target = 'right_'.$target;
                }

                $relationCascade = array_merge(array('persist'), $cascade);
                $attributes = array(
                    'fieldName'    => $camel,
                    'cascade'      => $relationCascade,
                    'targetEntity' => $targetClass,
                    'inversedBy'   => $inversedBy,
                    'joinTable' => array(
                        'name'   => $joinTable,
                        'joinColumns' => array(
                            array(
                                'name' => $source.'_id',
                                'referencedColumnName' => 'id',
                                'onDelete' => 'CASCADE'
                            )
                        ),
                        'inverseJoinColumns' => array(
                            array(
                                'name' => $target.'_id',
                                'referencedColumnName' => 'id',
                                'unique' => false,
                                'onDelete' => 'CASCADE'
                            )
                        )
                    )
                );

                if (!$isOwning) {
                    unset($attributes['joinTable'], $attributes['inversedBy']);
                    $attributes['mappedBy'] = $inversedBy;
                }

                $metadata->mapManyToMany($attributes);

                break;

            case Relation::ONE_TO_MANY:
                if (null === $inversedBy) {
                    # http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table

                    # Self relation case
                    if ($source == $target) {
                        $source = 'left_'.$source;
                        $target = 'right_'.$target;
                    }

                    $relationCascade = array_merge(array('persist'), $cascade);
                    $attributes = array(
                        'fieldName'     => $camel,
                        'cascade'       => $relationCascade,
                        'targetEntity'  => $targetClass,
                        'indexBy'       => $indexBy,
                        'joinTable' => array(
                            'name'   => $joinTable,
                            'joinColumns' => array(
                                array(
                                    'name' => $source.'_id',
                                    'referencedColumnName' => 'id',
                                    'onDelete' => 'CASCADE'
                                )
                            ),
                            'inverseJoinColumns' => array(
                                array(
                                    'name' => $target.'_id',
                                    'referencedColumnName' => 'id',
                                    'unique' => false,
                                    'onDelete' => 'CASCADE'
                                )
                            )
                        )
                    );

                    $metadata->mapManyToMany($attributes);
                } else {
                    $metadata->mapOneToMany(array(
                        'fieldName'     => $camel,
                        'targetEntity'  => $targetClass,
                        'mappedBy'      => $inversedBy,
                        'indexBy'       => $indexBy,
                        'orphanRemoval' => false,
                        'cascade'       => array('persist'),
                        'fetch'         => DoctrineClassMetadata::FETCH_EXTRA_LAZY
                    ));
                }

                break;

            case Relation::ONE_TO_ONE:
                $relationCascade = array_merge(array('persist'), $cascade);
                $attributes = array(
                    'fieldName'     => $camel,
                    'cascade'       => $relationCascade,
                    'targetEntity'  => $targetClass,
                    'inversedBy'    => $inversedBy,
                    'orphanRemoval' => false,
                    'joinColumns' => array(
                        array(
                            'name' => $camel.'_id',
                            'referencedColumnName' => 'id',
                            'onDelete' => ((is_array($cascade) && in_array('remove', $cascade)) ? 'CASCADE' : 'SET NULL')
                        )
                    )
                );

                if (!$isOwning) {
                    unset($attributes['joinColumns'], $attributes['inversedBy']);
                    $attributes['mappedBy'] = $inversedBy;
                }

                $metadata->mapOneToOne($attributes);

                break;

            case Relation::MANY_TO_ONE:
                $relationCascade = array_merge(array('persist'), $cascade);
                $attributes = array(
                    'fieldName'    => $camel,
                    'cascade'      => $relationCascade,
                    'targetEntity' => $targetClass,
                    'inversedBy'   => $inversedBy,
                    'joinColumns' => array(
                        array(
                            'name' => $camel.'_id',
                            'referencedColumnName' => 'id',
                            'onDelete' => ((is_array($cascade) && in_array('remove', $cascade)) ? 'CASCADE' : 'SET NULL')
                        )
                    )
                );

                $metadata->mapManyToOne($attributes);

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'relation';
    }
}

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

class RelationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'target_beam' => null,
            'target'      => null,
            'inversed_by' => null,
            'type'        => null,
            'is_external' => null,
            'required'    => false
        ));
    }

    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        // We do not edit relation there anymore, use schema class instead

        $types = array('one-to-many', 'many-to-one', 'many-to-many', 'one-to-one');
        $types = array_combine($types, $types);

        $builder
            ->add($builder->create('relations', 'alert', array(
                'attr' => array(
                    'class' => 'alert-warning text-center'
                ),
                'label' => 'pum.form.alert.object.relations'
            )))
            ->add('target_beam', 'hidden')
            ->add('target', 'hidden')
            ->add('inversed_by', 'hidden')
            ->add('type', 'hidden')
            ->add('is_external', 'hidden')
            ->add('required', 'checkbox', array('required' => false))
        ;

        /*$builder
            ->add('target_beam', 'text', array(
                'read_only' => true
            ))
            ->add('target', 'text', array(
                'label' => 'Target Object',
                'read_only' => true
            ))
            ->add('inversed_by', 'text', array(
                'read_only' => true
            ))
            ->add('type', 'choice', array(
                'choices' => $types,
                'read_only' => true
            ))
            ->add('is_external', 'checkbox')
        ;*/
    }

    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $targetClass = $context->getObjectFactory()->getClassName($context->getProject()->getName(), $context->getOption('target'));
        $form->add($context->getField()->getCamelCaseName(), 'pum_object_entity', array(
            'class'        => $targetClass,
            'multiple'     => in_array($context->getOption('type') , array('one-to-many', 'many-to-many')),
            'project'      => $context->getProject()->getName(),
            'label'        => $formViewField->getLabel(),
            'allow_add'    => $formViewField->getOption('allow_add'),
            'allow_select' => $formViewField->getOption('allow_select'),
            'ajax'         => $formViewField->getOption('form_type') == 'ajax',
            'required'     => $context->getOption('required'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormViewOptions(FormBuilderInterface $builder, FormViewField $formViewField)
    {
        $builder
            ->add('form_type', 'choice', array(
                'choices'   =>  array(
                    'static' => 'pa.form.formview.fields.entry.options.form.type.types.static'/*'Regular select list'*/,
                    'ajax'   => 'pa.form.formview.fields.entry.options.form.type.types.ajax'/*'Ajax list'*/
                )
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
        $cb      = $context->getClassBuilder();
        $camel   = $context->getField()->getCamelCaseName();
        $factory = $context->getObjectFactory();
        $target  = $context->getOption('target');

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

        $cb->createProperty($camel);

        $cb->createMethod('get'.ucfirst($camel), '', '
            return $this->'.$camel.';
        ');

        $type = $context->getOption('type');

        if ($type == 'one-to-many' || $type == 'many-to-many') {
            if (substr($camel, -1) === 's') {
                $singular = substr($camel, 0, -1);
            } else {
                $singular = $camel;
            }

            $cb->prependOrCreateMethod('__construct', '', '
                $this->'.$camel.' = new \Doctrine\Common\Collections\ArrayCollection();
            ');
            $cb->createMethod('add'.ucfirst($singular), $class.' $'.$singular, '
                $this->get'.ucfirst($camel).'()->add($'.$singular.');

                return $this;
            ');

            $cb->createMethod('remove'.ucfirst($singular), $class.' $'.$singular, '
                $this->get'.ucfirst($camel).'()->removeElement($'.$singular.');

                return $this;
            ');

            $cb->createMethod('get'.ucfirst($camel).'By', 'array $criterias, $page = null, $per_page = null, $order_by = null, $order_type = "ASC"', '
                $criteria = \Doctrine\Common\Collections\Criteria::create();
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
                if (null !== $page && null !== $per_page) {
                    $criteria->setFirstResult(($page-1)*$per_page);
                    $criteria->setMaxResults($per_page);
                }
                if (null !== $order_by) {
                    $criteria->orderBy(array($order_by => $order_type));
                }

                return $this->'.$camel.'->matching($criteria);
            ');

        } else {
            $cb->createMethod('set'.ucfirst($camel), $class.' $'.$camel, '
                $this->'.$camel.' = $'.$camel.';

                return $this;
            ');
        }
    }

    public function mapDoctrineField(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $camel   = $context->getField()->getCamelCaseName();
        $factory = $context->getObjectFactory();
        $source  = $context->getField()->getObject()->getName();
        $target  = $context->getOption('target');

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

        $inversedBy  = Namer::toCamelCase($context->getOption('inversed_by'));
        if ($inversedBy) {
            try {
                $inversedField = $context->getProject()->getObject($target)->getField($inversedBy);
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

        $type = $context->getOption('type');
        $joinTable = 'obj__'.$context->getProject()->getLowercaseName().'__assoc__'.$context->getField()->getObject()->getLowercaseName().'__'.$context->getField()->getLowercaseName();

        switch ($type) {
            case 'many-to-many':
                # Self relation case
                if ($source == $target) {
                    $source = 'left_'.$source;
                    $target = 'right_'.$target;
                }

                $metadata->mapManyToMany(array(
                    'fieldName'    => $camel,
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
                ));

                break;

            case 'one-to-many':
                if (null === $inversedBy) {
                    # http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table

                    # Self relation case
                    if ($source == $target) {
                        $source = 'left_'.$source;
                        $target = 'right_'.$target;
                    }

                    $metadata->mapManyToMany(array(
                        'fieldName'    => $camel,
                        'targetEntity' => $targetClass,
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
                    ));
                } else {
                    $metadata->mapOneToMany(array(
                        'fieldName'     => $camel,
                        'targetEntity'  => $targetClass,
                        'mappedBy'      => $inversedBy,
                        'fetch'         => DoctrineClassMetadata::FETCH_EXTRA_LAZY
                    ));
                }

                break;

            case 'one-to-one':
            case 'many-to-one':
                $metadata->mapManyToOne(array(
                    'fieldName'    => $camel,
                    'targetEntity' => $targetClass,
                    'joinColumns' => array(
                        array(
                            'name' => $camel.'_id',
                            'referencedColumnName' => 'id',
                            'onDelete' => 'SET NULL'
                        )
                    )
                ));

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

<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Media as MediaConstraints;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class MediaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel.'_id');
        $cb->createProperty($camel.'_name');
        $cb->createProperty($camel.'_file'); // not persisted

        $cb->createMethod('get'.ucfirst($camel), '', '
            if (null === $this->'.$camel.'_id) {
                return null;
            }

            return new \Pum\Bundle\TypeExtraBundle\Model\Media($this->'.$camel.'_id, $this->'.$camel.'_name, $this->'.$camel.'_file);
        ');

        $cb->createMethod('set'.ucfirst($camel), '\Pum\Bundle\TypeExtraBundle\Model\Media $'.$camel, '
            $this->'.$camel.'_id = $'.$camel.'->getId();
            $this->'.$camel.'_name = $'.$camel.'->getName();
            $this->'.$camel.'_final_name = $'.$camel.'->getFinalName();
            $this->'.$camel.'_file = $'.$camel.'->getFile();

            return $this;
        ');

        if (!$cb->hasImplements('Pum\Bundle\TypeExtraBundle\Media\FlushStorageInterface')) {
            $cb->addImplements('Pum\Bundle\TypeExtraBundle\Media\FlushStorageInterface');

            $cb->createMethod('flushToStorage', 'Pum\Bundle\TypeExtraBundle\Media\StorageInterface $storage', '
                if (null !== $this->'.$camel.'_file) {
                    if (null !== $this->'.$camel.'_id) {
                        $storage->remove($this->'.$camel.'_id);
                        $this->'.$camel.'_id = null;
                    }
                    $this->'.$camel.'_id = $storage->store($this->'.$camel.'_file);
                }
                if (isset($this->'.$camel.'_final_name)) {
                    $this->'.$camel.'_name = $this->'.$camel.'_final_name;
                }
            ');
        } else if ($cb->hasMethod('flushToStorage')) {
            $flushToStorageMethod = $cb->getMethod('flushToStorage');

            if ($flushToStorageMethod->getArguments() == 'Pum\Bundle\TypeExtraBundle\Media\StorageInterface $storage') {
                $flushToStorageMethod->appendCode('if (null !== $this->'.$camel.'_file) {
                    if (null !== $this->'.$camel.'_id) {
                        $storage->remove($this->'.$camel.'_id);
                        $this->'.$camel.'_id = null;
                    }
                    $this->'.$camel.'_id = $storage->store($this->'.$camel.'_file);
                }
                if (isset($this->'.$camel.'_final_name)) {
                    $this->'.$camel.'_name = $this->'.$camel.'_final_name;
                }');
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'maxsize_value' => null,
            'maxsize_unit'  => 'M',
            'type'          => 'file',
            'label'         => null,
            'required'      => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('type', 'choice', array(
                    'label'     => 'Media type',
                    'choices'   => array(
                        'image' => 'Image',
                        'video' => 'Video',
                        'pdf'   => 'PDF',
                        'file'  => 'File',
                    ),
                    'empty_value' => 'Choose your type',
            ))
            ->add('maxsize_value', 'number', array('required' => false))
            ->add('maxsize_unit', 'choice', array(
                    'choices'   => array(
                        'k' => 'Ko',
                        'M' => 'M',
                    )
             ))
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $name = $context->getField()->getLowercaseName();
        $camel = $context->getField()->getCamelCaseName();

        $metadata->mapField(array(
            'fieldName' => $name.'_name',
            'type'      => 'string',
            'length'    => 100,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'fieldName' => $name.'_id',
            'type'      => 'string',
            'length'    => 512,
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form, FormViewField $formViewField)
    {
        $form->add($context->getField()->getCamelCaseName(), 'pum_media', array(
            'label' => $formViewField->getLabel(),
            'attr'  => array(
                'placeholder' => $formViewField->getPlaceholder()
            ),
            'required' => $context->getOption('required')
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $choicesKey = array('1', '0');
        $choicesValue = array('Has media', 'Has no media');

        $builder
            ->add('value', 'choice', array(
                'choices'  => array_combine($choicesKey, $choicesValue)
            ))
        ;
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(FieldContext $context, QueryBuilder $qb, $order)
    {
        $field = $qb->getRootAlias() . '.' . $context->getField()->getCamelCaseName().'_name';
        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter)
    {
        if (!isset($filter['value']) || is_null($filter['value'])) {
            return $qb;
        }

        $name = $context->getField()->getCamelCaseName();

        $parameterKey = count($qb->getParameters());

        if ($filter['value']) {
            $operator = '!=';
        } else {
            $operator = '=';
        }

        $qb
            ->andWhere($qb->getRootAlias().'.'.$name.'_id'.' '.$operator.' ?'.$parameterKey)
            ->setParameter($parameterKey, "");

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $options = array('type' => $context->getOption('type'));
        if ($context->getOption('maxsize_value')) {
            $options['maxSize'] = $context->getOption('maxsize_value').$context->getOption('maxsize_unit');
        }

        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new MediaConstraints($options));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media';
    }
}

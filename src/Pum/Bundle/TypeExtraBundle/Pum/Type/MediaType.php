<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Media as MediaConstraints;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class MediaType extends AbstractType
{
    protected $storage;

    public function __construct(StorageInterface $storage = null)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel.'_media'); // not persisted
        $cb->createProperty($camel.'_id');
        $cb->createProperty($camel.'_name');

        $cb->createMethod('get'.ucfirst($camel), '', '
            if (null === $this->'.$camel.'_media) {
                $this->'.$camel.'_media = new \Pum\Bundle\TypeExtraBundle\Model\Media($this->'.$camel.'_id, $this->'.$camel.'_name);
            }

            return $this->'.$camel.'_media;
        ');

        $cb->createMethod('set'.ucfirst($camel), '\Pum\Bundle\TypeExtraBundle\Model\Media $'.$camel, '
            $this->'.$camel.'_media = $'.$camel.';

            return $this;
        ');

        $cb->createMethod('update'.ucfirst($camel), '', '
            if (null === $this->'.$camel.'_media) {
                return;
            }

            $this->'.$camel.'_id   = $this->'.$camel.'_media->getId();
            $this->'.$camel.'_name = $this->'.$camel.'_media->getName();
        ');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'maxsize_value' => null,
            'maxsize_unit'  => 'M',
            'type'          => 'file'
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $name = $context->getField()->getLowercaseName();
        $camel = $context->getField()->getCamelCaseName();

        $metadata->addLifecycleCallback('update'.ucfirst($camel), 'postFlush');

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
    public function buildForm(FieldContext $context, FormInterface $form)
    {
        $name = $field->getLowercaseName();

        $form->add($name, 'pum_media');
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $choicesKey = array(null, '1', '0');
        $choicesValue = array('All', 'Has media', 'Has no media');

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
        $name = $field->getLowercaseName();

        $field = $qb->getRootAlias() . '.' . $name.'_name';
        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(FieldContext $context, QueryBuilder $qb, $filter)
    {
        $name = $field->getLowercaseName();

        if (isset($values['value']) && !is_null($values['value'])) {
            $parameterKey = count($qb->getParameters());

            if ($values['value']) {
                $operator = '!=';
            } else {
                $operator = '=';
            }

            $qb
                ->andWhere($qb->getRootAlias().'.'.$name.'_id'.' '.$operator.' ?'.$parameterKey)
                ->setParameter($parameterKey, "");
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $maxSize = $context->getOption('maxsize_value');
        $metadata->addGetterConstraint($name, new MediaConstraints(array('type' => $options['type'], 'maxSize' => $maxSize)));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media';
    }
}

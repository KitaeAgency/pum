<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Media as MediaConstraints;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Definition\FieldDefinition;
use Pum\Extension\EmFactory\EmFactoryFeatureInterface;
use Pum\Extension\ProjectAdmin\ProjectAdminFeatureInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class MediaType extends AbstractType implements EmFactoryFeatureInterface, ProjectAdminFeatureInterface
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
    public function mapDoctrineField(FieldDefinition $field, DoctrineClassMetadata $metadata)
    {
        $name = $context->getLowercaseName();

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
    public function buildForm(FieldDefinition $field, FormBuilderInterface $builder)
    {
        $name = $field->getLowercaseName();

        $form->add($name, 'pum_media');
    }

    /**
     * {@inheritdoc}
     */
    public function buildFilterForm(FieldDefinition $field, FormBuilderInterface $builder)
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
    public function addOrderCriteria(FieldDefinition $field, QueryBuilder $qb, $order)
    {
        $name = $field->getLowercaseName();

        $field = $qb->getRootAlias() . '.' . $name.'_name';
        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(FieldDefinition $field, QueryBuilder $qb, $filter)
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
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $maxSize = ($options['maxsize_value']) ? $options['maxsize_value'].$options['maxsize_unit'] : null;
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

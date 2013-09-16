<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Pum\Core\Type\AbstractType;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Media as MediaConstraints;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Doctrine\ORM\QueryBuilder;

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
    public function buildOptionsForm(FormInterface $form)
    {
        $form
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
    public function getRawColumns($name, array $options)
    {
        return array($name.'_name', $name.'_id');
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
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
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $maxSize = ($options['maxsize_value']) ? $options['maxsize_value'].$options['maxsize_unit'] : null;
        $metadata->addGetterConstraint($name, new MediaConstraints(array('type' => $options['type'], 'maxSize' => $maxSize)));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'pum_media');
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormFilter(FormInterface $form)
    {
        $choicesKey = array(null, '1', '0');
        $choicesValue = array('All', 'Has media', 'Has no media');

        $form
            ->add('value', 'choice', array(
                'choices'  => array_combine($choicesKey, $choicesValue)
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function writeValue(Object $object, $value, $name, array $options)
    {
        if (null === $value) {
            $object->set($name.'_name', null);
            $object->set($name.'_id', null);
        }

        if (!$value instanceof Media) {
            throw new \InvalidArgumentException(sprintf('Expected a Media, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->set($name.'_name', $value->getName());
        $object->set($name.'_id', $value->getId());

        $value->flushStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $idValue   = $object->get($name.'_id');
        $nameValue = $object->get($name.'_name');

        $media = new Media($this->storage, $idValue, $nameValue);

        return $media;
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name.'_name';

        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values)
    {
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
}

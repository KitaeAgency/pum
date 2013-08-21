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
    public function writeValue(Object $object, $value, $name, array $options)
    {
        if (null === $value) {
            $object->__pum__rawSet($name.'_name', null);
            $object->__pum__rawSet($name.'_id', null);
        }

        if (!$value instanceof Media) {
            throw new \InvalidArgumentException(sprintf('Expected a Media, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->__pum__rawSet($name.'_name', $value->getName());
        $object->__pum__rawSet($name.'_id', $value->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $_name = $object->__pum__rawGet($name.'_name');
        $id    = $object->__pum__rawGet($name.'_id');
        $file  = $object->__pum__rawGet($name.'_file');

        $media = new Media($_name, $id, $file);
        $media->setStorage($this->storage);

        return $media;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptionsType()
    {
        return 'ww_field_type_media';
    }

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
        $form->add($name.'_name', 'text');
        $form->add($name.'_id', 'text', array('label' => ucfirst($name) . " filename", "disabled" => true));
        $form->add($name.'_file', 'file');
    }
}

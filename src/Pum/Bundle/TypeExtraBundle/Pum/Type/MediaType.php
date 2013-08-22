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
    public function writeValue(Object $object, $value, $name, array $options)
    {
        if (null === $value) {
            $object->_pumRawSet($name.'_name', null);
            $object->_pumRawSet($name.'_id', null);
        }

        if (!$value instanceof Media) {
            throw new \InvalidArgumentException(sprintf('Expected a Media, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->_pumRawSet($name.'_name', $value->getName());
        $object->_pumRawSet($name.'_id', $value->getId());

        $value->flushStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $name  = $object->_pumRawGet($name.'_name');
        $id    = $object->_pumRawGet($name.'_id');

        $media = new Media($this->storage, $id);

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
        $form->add($name.'_file', 'file', array('property_path' => 'avatar.file'));
    }
}

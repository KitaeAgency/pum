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

class MediaType extends AbstractType
{
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
            'fieldName' => $name.'_path',
            'type'      => 'string',
            'length'    => 100,
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
            $object->__pum__rawSet($name.'_path', null);
        }

        if (!$value instanceof Media) {
            throw new \InvalidArgumentException(sprintf('Expected a Media, got a "%s".', is_object($value) ? get_class($value) : gettype($value)));
        }

        $object->__pum__rawSet($name.'_name', $value->getName());
        $object->__pum__rawSet($name.'_path', $value->getPath());
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        $name = $object->__pum__rawGet($name.'_name');
        $path = $object->__pum__rawGet($name.'_path');

        if (null === $name && null === $path) {
            return null;
        }

        return new Media($name, $path);
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
        $metadata->addGetterConstraint($name, new MediaConstraints());
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name.'_name', 'text');
        $form->add($name.'_path', 'text', array('label' => ucfirst($name) . " path", "disabled" => true));
        $form->add($name.'_file', 'file');
    }
}

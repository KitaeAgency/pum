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
        $cb->createProperty($camel.'_mime');
        $cb->createProperty($camel.'_width');
        $cb->createProperty($camel.'_height');
        $cb->createProperty($camel.'_file'); // not persisted

        if (!$cb->hasProperty('storageToRemove')) {
            $cb->createProperty('storageToRemove', 'null');
        }
        if (!$cb->hasMethod('getStorageToRemove')) {
            $cb->createMethod('getStorageToRemove', '', 'return $this->storageToRemove;');
        }
        if (!$cb->hasMethod('addStorageToRemove')) {
            $cb->createMethod('addStorageToRemove', '$id', 'if (null === $this->storageToRemove) {
                $this->storageToRemove = array();
            }
            $this->storageToRemove[] = $id;

            return $this;');
        }
        if (!$cb->hasMethod('cleanStorageToRemove')) {
            $cb->createMethod('cleanStorageToRemove', '', '$this->storageToRemove = null;

            return $this;');
        }

        $cb->createMethod('get'.ucfirst($camel), '', '
            if (null === $this->'.$camel.'_id) {
                return null;
            }

            return new \Pum\Bundle\TypeExtraBundle\Model\Media($this->'.$camel.'_id, $this->'.$camel.'_name, $this->'.$camel.'_file, $this->'.$camel.'_mime, $this->'.$camel.'_width, $this->'.$camel.'_height);
        ');

        $cb->createMethod('set'.ucfirst($camel), '\Pum\Bundle\TypeExtraBundle\Model\Media $'.$camel, '
            $this->'.$camel.'_id = $'.$camel.'->getId();
            $this->'.$camel.'_name = $'.$camel.'->getName();
            $this->'.$camel.'_mime = $'.$camel.'->getMime();
            $this->'.$camel.'_height = $'.$camel.'->getHeight();
            $this->'.$camel.'_width = $'.$camel.'->getWidth();
            $this->'.$camel.'_final_name = $'.$camel.'->getFinalName();
            $this->'.$camel.'_file = $'.$camel.'->getFile();

            return $this;
        ');

        $cb->createMethod('remove'.ucfirst($camel), '$deleteFile = true', '
            if (true === $deleteFile) {
                $this->addStorageToRemove($this->'.$camel.'_id);
            }

            $this->'.$camel.'_id = null;
            $this->'.$camel.'_name = null;
            $this->'.$camel.'_mime = null;
            $this->'.$camel.'_height = null;
            $this->'.$camel.'_width = null;
            $this->'.$camel.'_final_name = null;
            $this->'.$camel.'_file = null;

            return $this;
        ');

        $flushToStorageCode = '
                if ($this->'.$camel.'_file instanceof \SplFileInfo && $this->'.$camel.'_file->isFile()) {
                    if (null !== $this->'.$camel.'_id) {
                        $storage->remove($this->'.$camel.'_id);
                        $this->'.$camel.'_id = null;
                    }
                    $this->'.$camel.'_id = $storage->store($this->'.$camel.'_file);
                    $this->'.$camel.'_mime = $storage->guessMime($this->'.$camel.'_file);
                    list($this->'.$camel.'_width, $this->'.$camel.'_height) = $storage->guessImageSize($this->'.$camel.'_file);
                }
                if (isset($this->'.$camel.'_final_name)) {
                    $this->'.$camel.'_name = $this->'.$camel.'_final_name;
                }
            ';

        $removeFromStorageCode = '
                if (null === $storageToRemove && null !== $this->'.$camel.'_id) {
                    $storage->remove($this->'.$camel.'_id);
                }
            ';

        if (!$cb->hasImplements('Pum\Bundle\TypeExtraBundle\Media\FlushStorageInterface')) {

            $cb->addImplements('Pum\Bundle\TypeExtraBundle\Media\FlushStorageInterface');
            $cb->createMethod('flushToStorage', 'Pum\Bundle\TypeExtraBundle\Media\StorageInterface $storage', $flushToStorageCode);
            $cb->createMethod('removeFromStorage', 'Pum\Bundle\TypeExtraBundle\Media\StorageInterface $storage, $storageToRemove = null', 'if (null !== $storageToRemove) {
                foreach ($storageToRemove as $storageId) {
                    $storage->remove($storageId);
                }
                $this->cleanStorageToRemove();
            }

                '.$removeFromStorageCode);

        } else if ($cb->hasMethod('flushToStorage') && $cb->hasMethod('removeFromStorage')) {
            $flushToStorageMethod = $cb->getMethod('flushToStorage');

            if ($flushToStorageMethod->getArguments() == 'Pum\Bundle\TypeExtraBundle\Media\StorageInterface $storage') {
                $flushToStorageMethod->appendCode($flushToStorageCode);
            }

            $removeFromStorageMethod = $cb->getMethod('removeFromStorage');

            if ($removeFromStorageMethod->getArguments() == 'Pum\Bundle\TypeExtraBundle\Media\StorageInterface $storage, $storageToRemove = null') {
                $removeFromStorageMethod->appendCode($removeFromStorageCode);
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
                    'choices'   => array(
                        'image' => 'pum.form.field.type.name.type.medias.image',
                        'video' => 'pum.form.field.type.name.type.medias.video',
                        'pdf'   => 'pum.form.field.type.name.type.medias.pdf',
                        'file'  => 'pum.form.field.type.name.type.medias.file',
                    ),
                    'empty_value' => true,
            ))
            ->add('maxsize_value', 'number', array('required' => false))
            ->add('maxsize_unit', 'choice', array(
                    'choices'   => array(
                        'k' => 'pum.form.field.type.name.maxsize.unit.units.kb',
                        'M' => 'pum.form.field.type.name.maxsize.unit.units.mb',
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
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_name',
            'fieldName' => $context->getField()->getCamelCaseName().'_name',
            'type'      => 'string',
            'length'    => 100,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_id',
            'fieldName' => $context->getField()->getCamelCaseName().'_id',
            'type'      => 'string',
            'length'    => 512,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_mime',
            'fieldName' => $context->getField()->getCamelCaseName().'_mime',
            'type'      => 'string',
            'length'    => 25,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_width',
            'fieldName' => $context->getField()->getCamelCaseName().'_width',
            'type'      => 'string',
            'length'    => 10,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_height',
            'fieldName' => $context->getField()->getCamelCaseName().'_height',
            'type'      => 'string',
            'length'    => 10,
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

<?php

namespace Pum\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Extension\Util\Namer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RelationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'target'      => null,
            'inversed_by' => null,
            'type'        => null,
        ));
    }

    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $types = array('one-to-many', 'many-to-one');
        $types = array_combine($types, $types);

        $builder
            ->add('target', 'text')
            ->add('inversed_by', 'text')
            ->add('type', 'choice', array('choices' => array($types)))
        ;
    }

    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $factory = $context->getObjectFactory();

        $target = $context->getOption('target');
        $class = $factory->getClassName($context->getProject()->getName(), $target);

        $cb->createProperty($camel);

        $cb->createMethod('get'.ucfirst($camel), '', '
            return $this->'.$camel.';

            return $this;
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
        } else {
            $cb->createMethod('set'.ucfirst($camel), $class.' $'.$camel, '
                $this->'.$camel.' = $'.$camel.';

                return $this;
            ');
        }
    }

    public function mapDoctrineField(FieldContext $context, DoctrineClassMetadata $metadata)
    {
        $camel = $context->getField()->getCamelCaseName();

        $factory = $context->getObjectFactory();

        $source = $context->getField()->getObject()->getName();

        $target      = $context->getOption('target');
        $targetClass = $factory->getClassName($context->getProject()->getName(), $target);
        $inversedBy  = $context->getOption('inversed_by');
        $type       = $context->getOption('type');

        $joinTable = 'obj__'.$context->getProject()->getLowercaseName().'__assoc__'.$context->getField()->getLowercaseName();

        switch ($type) {
            case 'one-to-many':
                if (null === $inversedBy) {
                    # http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table
                    $metadata->mapManyToMany(array(
                        'fieldName'    => $camel,
                        'targetEntity' => $targetClass,
                        'joinTable' => array(
                            'name'   => $joinTable,
                            'joinColumns' => array(array('name' => $source.'_id', 'referencedColumnName' => 'id')),
                            'inverseJoinColumns' => array(array('name' => $target.'_id', 'referencedColumnName' => 'id', 'unique' => true)),
                        )
                    ));
                } else {
                    $metadata->mapOneToMany(array(
                        'fieldName'    => $camel,
                        'targetEntity' => $targetClass,
                        'mappedBy'    => $inversedBy,
                    ));
                }

                break;

            case 'many-to-one':
                $metadata->mapManyToOne(array(
                    'fieldName'    => $camel,
                    'targetEntity' => $targetClass,
                    'joinColumns' => array(
                        array('name' => $camel.'_id', 'referencedColumnName' => 'id')
                    )
                ));

                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'relation';
    }
}

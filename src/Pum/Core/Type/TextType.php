<?php

namespace Pum\Core\Type;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

class TextType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'multi_lines'      => false,
            'unique'           => false,
            'required'         => false,
            'length'           => null,
            'min_length'       => 0,
            'max_length'       => null,
            '_doctrine_length' => function (Options $options) {
                return $options['max_length'] !== null ? $options['max_length'] : null;
            },
            '_doctrine_type' => function (Options $options) {
                return $options['max_length'] !== null ? 'string' : 'text';
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        $form
            ->add('unique', 'checkbox', array('required' => false))
            ->add('min_length', 'number', array('required' => false))
            ->add('max_length', 'number', array('required' => false))
            ->add('multi_lines', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

        /**
     * {@inheritdoc}
     */
    public function buildFormFilter(FormInterface $form)
    {
        $filterTypes = array(null, '=', '<>', 'LIKE', 'BEGIN', 'END');
        $filterNames = array('Choose an operator', 'equal', 'different', 'containts', 'starting with', 'ending with');

        $form
            ->add('type', 'choice', array(
                'choices'  => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => $options['_doctrine_type'],
            'length'    => $options['_doctrine_length'],
            'nullable'  => true,
            'unique'    => $options['unique'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        if ($options['required']) {
            $metadata->addGetterConstraint($name, new NotBlank());
        }

        if ($options['min_length'] || $options['max_length']) {
            $metadata->addGetterConstraint($name, new Length(array('min' => $options['min_length'], 'max' => $options['max_length'])));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $options = $this->resolveOptions($options);

        $form->add($name, $options['multi_lines'] ? 'textarea' : 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values)
    {
        if (isset($values['type']) && isset($values['value'])) {
            if ($values['type'] === 'LIKE') {
                $values['value'] = '%'.$values['value'].'%';
            } elseif ($values['type'] === 'BEGIN') {
                $values['type'] = 'LIKE';
                $values['value'] = $values['value'].'%';
            } elseif ($values['type'] === 'END') {
                $values['type'] = 'LIKE';
                $values['value'] = '%'.$values['value'];
            }

            $parameterKey = count($qb->getParameters());
            $qb
                ->andWhere($qb->getRootAlias().'.'.$name.' '.$values['type'].' ?'.$parameterKey)
                ->setParameter($parameterKey, $values['value']);
        }

        return $qb;
    }
}

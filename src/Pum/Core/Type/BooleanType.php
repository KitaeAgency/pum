<?php

namespace Pum\Core\Type;

use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Type;
use Doctrine\ORM\QueryBuilder;

class BooleanType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        $form
            ->add('unique', 'checkbox', array('required' => false))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormFilter(FormInterface $form)
    {
        $choicesKey = array(null, '1', '0');
        $choicesValue = array('All', 'Yes', 'No');

        $form
            ->add('value', 'choice', array(
                'choices'  => array_combine($choicesKey, $choicesValue)
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        $unique = isset($options['unique']) ? $options['unique'] : false;

        $metadata->mapField(array(
            'fieldName' => $name,
            'type'      => 'boolean',
            'nullable'  => true,
            'unique'    => $unique,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        $metadata->addGetterConstraint($name, new Type(array('type' => 'boolean')));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        $form->add($name, 'checkbox', array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values)
    {
        if (isset($values['value']) && !is_null($values['value'])) {
            $parameterKey = count($qb->getParameters());
            $qb
                ->andWhere($qb->getRootAlias().'.'.$name.' = ?'.$parameterKey)
                ->setParameter($parameterKey, $values['value']);
        }

        return $qb;
    }
}

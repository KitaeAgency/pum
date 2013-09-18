<?php

namespace Pum\Core\Type;

use Pum\Core\Exception\FeatureNotImplementedException;
use Pum\Core\Extension\EmFactory\Doctrine\Metadata\ObjectClassMetadata;
use Pum\Core\Object\Object;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

/**
 * Base class to reduce amount of code in types.
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * Facility method to cleanup options passed to the form.
     *
     * @return array
     */
    public function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->setDefaultOptions($resolver);

        return $resolver->resolve($options);
    }

    /**
     * Configure the option resolver for resolveOptions.
     */
    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormInterface $form)
    {
        throw new FeatureNotImplementedException('getFormOptionsType for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineFields(ObjectClassMetadata $metadata, $name, array $options)
    {
        throw new FeatureNotImplementedException('mapDoctrineFields for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(ClassMetadata $metadata, $name, array $options)
    {
        throw new FeatureNotImplementedException('mapValidation for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormInterface $form, $name, array $options)
    {
        throw new FeatureNotImplementedException('buildForm for '.get_class($this));
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormFilter(FormInterface $form)
    {
        $filterTypes = array(null, '=', '<>');
        $filterNames = array('Choose an operator', 'equal', 'different');

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
    public function writeValue(Object $object, $value, $name, array $options)
    {
        $object->_pumRawSet($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function readValue(Object $object, $name, array $options)
    {
        return $object->_pumRawGet($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRawColumns($name, array $options)
    {
        return array($name);
    }

    /**
     * @return QueryBuilder;
     */
    public function addOrderCriteria(QueryBuilder $qb, $name, array $options, $order)
    {
        $field = $qb->getRootAlias() . '.' . $name;

        $qb->orderby($field, $order);

        return $qb;
    }

    /**
     * @return QueryBuilder;
     */
    public function addFilterCriteria(QueryBuilder $qb, $name, array $values)
    {
        if (!isset($values['type']) || !$values['type']) {
            return $qb;
        }
        if (!isset($values['value']) || !$values['value']) {
            return $qb;
        }

        if (in_array($values['type'], array('<', '>', '<=', '>=', '<>', '=', 'LIKE'))) {
            $operator = $values['type'];
        } else {
            throw new \InvalidArgumentException(sprintf('Unexpected filter type "%s".', $values['type']));
        }

        $parameterKey = count($qb->getParameters());

        return $qb
            ->andWhere($qb->getRootAlias().'.'.$name.' '.$operator.' ?'.$parameterKey)
            ->setParameter($parameterKey, $values['value'])
        ;
    }
}

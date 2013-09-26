<?php

namespace Pum\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Validator\Constraints\DateTime as DateTimeConstraints;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class DateType extends AbstractType
{
    const DATE_FORMAT    = "dd/MM/yyyy";
    const JS_DATE_FORMAT = "dd/mm/yy";

    const ANTERIOR_DATE  = 'anterior';
    const POSTERIOR_DATE = 'posterior';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            '_doctrine_type'   => 'date',
            'unique'           => false,
            'restriction'      => null,
            'required'         => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('unique', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
            ->add('restriction', 'choice', array(
                    'required' => false,
                    'choices'   => array(
                            self::ANTERIOR_DATE  => 'Allow only anterior date',
                            self::POSTERIOR_DATE => 'Allow only posterior date'
                    ),
                    'empty_value' => 'No restriction',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel);

        $cb->createMethod('get'.ucfirst($camel), '', '
            return $this->'.$camel.';
        ');

        $cb->createMethod('set'.ucfirst($camel), '\DateTime $'.$camel, '
            $this->'.$camel.' = $'.$camel.';

            return $this;
        ');
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'fieldName' => $context->getField()->getCamelCaseName(),
            'name'      => $context->getField()->getLowercaseName(),
            'type'      => $context->getOption('_doctrine_type'),
            'nullable'  => true,
            'unique'    => $context->getOption('unique'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), new DateTimeConstraints(array('restriction' => $context->getOption('restriction'))));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FieldContext $context, FormInterface $form)
    {
        $restriction = $context->getOption('restriction');

        if ($restriction === Date::ANTERIOR_DATE) {
            $yearsRange = "-70:+0";
            $minDate = new \DateTime("-70 years");
            $maxDate = new \DateTime();
        } elseif ($options['restriction'] === Date::POSTERIOR_DATE) {
            $yearsRange = "-0:+70";
            $minDate = new \DateTime();
            $maxDate = new \DateTime("+70 years");
        } else {
            $yearsRange = "-35:+35";
            $minDate = new \DateTime("-35 years");
            $maxDate = new \DateTime("+35 years");
        }

        $form->add($name, 'date', array(
            'widget' => 'single_text',
            'format' => self::DATE_FORMAT,
            'attr' => array(
                'class' => 'datepicker',
                'data-yearrange' => $yearsRange,
                'data-mindate'     => $minDate->format("U"),
                'data-maxdate'     => $maxDate->format("U"),
                'data-dateFormat'  => DateType::JS_DATE_FORMAT
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'date';
    }
}

<?php

namespace Pum\Core\Extension\Core\Type;

use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationClassMetadata;

class PasswordType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'required'    => false,
            'label'       => null,
            'placeholder' => null
        ));
    }

    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
    {
        $form->add($context->getField()->getCamelCaseName(), 'pum_password', array(
            'label' => $formViewField->getLabel(),
            'attr'  => array(
                'placeholder' => $formViewField->getPlaceholder(),
            ),
            'required' => $context->getOption('required'),
            'repeated' => $formViewField->getOption('repeated', false),
            'disabled' => $formViewField->getDisabled(),
        ));
    }

    public function buildField(FieldBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $name = $context->getField()->getCamelCaseName();

        $cb->addImplements('Pum\Core\Extension\Security\PumPasswordInterface');

        $cb->createProperty($name.'Salt');
        $cb->createProperty($name);
        $cb->addGetMethod($name);
        $cb->addGetMethod($name.'Salt');

        $setMethod = 'set'.ucfirst($name);
        $cb->createMethod($setMethod, '$raw, $encoder', <<<METHOD
            if (\$encoder instanceof \Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface && \$this instanceof \Pum\Core\Extension\Security\PumPasswordInterface) {
                \$encoder = \$encoder->getEncoder(\$this);
            }

            if (!\$encoder instanceof \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface) {
                throw new \InvalidArgumentException(sprintf('Expected a PasswordEncoderInterface, got a "%s".', is_object(\$encoder) ? get_class(\$encoder) : gettype(\$encoder)));
            }

            \$this->${name}Salt = md5(mt_rand().uniqid().microtime());
            \$this->${name} = \$encoder->encodePassword(\$raw, \$this->${name}Salt);

            return \$this;
METHOD
        );
    }

    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'string',
            'length'    => 100,
            'nullable'  => true,
        ));

        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName().'_salt',
            'fieldName' => $context->getField()->getCamelCaseName().'Salt',
            'type'      => 'string',
            'length'    => 100,
            'nullable'  => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('required', 'checkbox', array('required' => false))
        ;
    }

     /**
    * {@inheritdoc}
    */
    public function buildFilterForm(FormBuilderInterface $builder)
    {
        $filterTypes = array('=');
        $filterNames = array('pa.form.tableview.columns.entry.filters.entry.type.types.equal');

        $builder
            ->add('type', 'choice', array(
                'choices' => array_combine($filterTypes, $filterNames)
            ))
            ->add('value', 'text')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormViewOptions(FormBuilderInterface $builder, FormViewField $formViewField)
    {
        $builder
            ->add('repeated', 'checkbox', array('required' => false))
        ;
    }

    public function mapValidation(FieldContext $context, ValidationClassMetadata $metadata)
    {
        $required   = $context->getOption('required');

        if ($required) {
            $constraint = new NotBlank();
            $metadata->addGetterConstraint($context->getField()->getCamelCaseName(), $constraint);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'password';
    }
}

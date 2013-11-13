<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\ObjectDefinition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;

class ObjectDefinitionSecurityUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // here, we're not able to use form events because form inherits data.
        // That's a limitation, because of:
        // https://github.com/symfony/symfony/issues/8607

        $fields = array();
        $objectDefinition = $options['objectDefinition'];
        foreach ($objectDefinition->getFields() as $field) {
            if ($field->getType() != 'relation') {
                $fields[] = $field;
            }
        }

        $builder
            ->add('securityUserEnabled', 'checkbox', array('label' => 'Enabled'))
            ->add('securityUsernameField', 'entity', array(
                'label'       => 'Username field',
                'class'       => 'Pum\Core\Definition\FieldDefinition',
                'choice_list' => new ObjectChoiceList($fields, 'name', array(), 'object.name', 'name'),
                'required'    => false,
                'empty_value' => 'Select the username field',
            ))
            ->add('securityPasswordField', 'entity', array(
                'label'       => 'Password field',
                'class'       => 'Pum\Core\Definition\FieldDefinition',
                'choice_list' => new ObjectChoiceList($fields, 'name', array(), 'object.name', 'name'),
                'required'    => false,
                'empty_value' => 'Select the password field'
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true
        ));

        $resolver->setRequired(array('objectDefinition'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_security_user';
    }
}

<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Bundle\WoodworkBundle\Form\Listener\TypeOptionsListener;
use Pum\Core\SchemaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldDefinitionType extends AbstractType
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('type', 'choice', array('choices' => $this->getTypeChoice()))
            ->addEventSubscriber(new TypeOptionsListener($this->schemaManager))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'   => 'Pum\Core\Definition\FieldDefinition'
        ));
    }

    public function getName()
    {
        return 'ww_field_definition';
    }

    private function getTypeChoice()
    {
        $types = array();
        foreach ($this->schemaManager->getTypeNames() as $typeName) {
            $types[$typeName] = ucfirst($typeName);
        }

        return $types;
    }
}

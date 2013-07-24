<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\SchemaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldTypeType extends AbstractType
{
    /**
     * @var SchemaManager
     */
    protected $schemaManager;

    public function __construct(SchemaManager $schemaManager)
    {
        $this->schemaManager = $schemaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getTypeChoice()
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_field_type';
    }

    /**
     * Internal method return choice list for type.
     *
     * @return array associative array (type => label)
     */
    private function getTypeChoice()
    {
        $types = array();
        foreach ($this->schemaManager->getTypeNames() as $typeName) {
            $types[$typeName] = ucfirst($typeName);
        }

        return $types;
    }
}

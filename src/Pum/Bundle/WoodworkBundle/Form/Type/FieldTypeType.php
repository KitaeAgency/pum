<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\ObjectFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldTypeType extends AbstractType
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getTypeChoice(),
            'translation_domain' => 'pum_form'
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
        foreach ($this->objectFactory->getTypeNames() as $typeName) {
            $types[$typeName] = ucfirst($typeName);
        }

        return $types;
    }
}

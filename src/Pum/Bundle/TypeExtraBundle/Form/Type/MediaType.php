<?php

namespace Pum\Bundle\TypeExtraBundle\Form\Type;

use Pum\Bundle\TypeExtraBundle\Model\Media;
use Pum\Bundle\TypeExtraBundle\Validator\Constraints\Media as MediaConstraints;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['show_name']) {
            $builder->add('name', 'text');
        }
        $builder->add('file', 'file');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Bundle\TypeExtraBundle\Model\Media',
            'type' => '',
            'show_name' => true,
            'constraints' => function (Options $options) {
                $constraints = array();

                if ($options['type']) {
                    $constraints[] = new MediaConstraints(array('type' => $options['type']));
                }

                return $constraints;
            }
        ));

        $resolver->setAllowedTypes(array('type' => 'string'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pum_media';
    }
}

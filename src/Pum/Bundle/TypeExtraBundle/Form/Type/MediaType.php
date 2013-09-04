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
use Symfony\Component\Validator\Constraints\File;
use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;

/**
 * We manually map coordinates because it's a value object (not modifiable).
 */
class MediaType extends AbstractType
{
    protected $storage;

    public function __construct(StorageInterface $storage = null)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text')
            ->add('file', 'file')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Pum\Bundle\TypeExtraBundle\Model\Media',
            'type' => '',
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

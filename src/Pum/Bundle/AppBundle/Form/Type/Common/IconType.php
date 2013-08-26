<?php

namespace Pum\Bundle\AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IconType extends AbstractType
{
    protected $iconChoices = array(
        'sample' => array('pencil', 'paperplane', 'thumbs-up', 'chat', 'rss', 'tools', 'search', 'newspaper', 'earth','shipping','gamepad-2','star-5','heart-3','bug','gift','cart-2','tags','folder-open','stack','home-2','music-2','map','location','mail','trophy', 'lab-2','tie','football','eight-ball','bowling','bowling-pin','baseball','soccer')
    );

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'icon_choices' => array('sample'),
            'expanded' => true,
            'choices' => function (Options $options) {
                $result = array();

                foreach ($options['icon_choices'] as $choice) {
                    if (!isset($this->iconChoices[$choice])) {
                        throw new \InvalidArgumentException(sprintf('Icon choice "%s" does not exist. Available are: %s.', $choice, implode(', ', array_keys($this->iconChoices))));
                    }
                    $result = array_merge($result, $this->iconChoices[$choice]);
                }

                return array_combine($result, $result);
            }
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
        return 'pum_icon';
    }
}

<?php

namespace Pum\Bundle\AppBundle\Form\Type\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IconType extends AbstractType
{
    protected $iconChoices = array(
        'sample' => array('meter', 'pencil2', 'images', 'calendar', 'cloud', 'chat', 'rss', 'newspaper2','tags','aid','bug','folder-open', 'user', 'house', 'office', 'thumbs-up', 'binoculars', 'earth','gift','cart2','truck','food','star5','heart3','music2','map2','location','mail','trophy', 'lab', 'tv', 'tags', 'library', 'books', 'pacman', 'dice', 'vcard')
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

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ($form->isRoot() || !$form->getParent()->has('color')) {
            $color = null;
        } else {
            $color = $form->getParent()->get('color')->getData();
        }

        $view->vars['color'] = $color;
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

<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Type;

use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\ObjectFactory;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PumObjectEntityType extends AbstractType
{
    protected $objectFactory;
    protected $emFactory;

    public function __construct(ObjectFactory $objectFactory, EmFactory $emFactory)
    {
        $this->objectFactory = $objectFactory;
        $this->emFactory     = $emFactory;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_add'] = $options['allow_add'];
        $view->vars['allow_select'] = $options['allow_select'];
        $view->vars['class'] = $options['class'];
        $view->vars['ajax'] = $options['ajax'];
        if ($options['ajax']) {
            $key = $view->vars['name'];
            $parent = $view;
            while ($parent = $parent->parent) {
                if ($parent->parent === null) {
                    break;
                }
                $key = $parent->vars['name'].'.'.$key;
            }
            $view->vars['ajax_id'] = $key;
        }
    }

    public function getParent()
    {
        return 'entity';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        // disable EM normalizer
        $resolver->setNormalizers(array('em' => function (Options $options, $val) { return $val; }));

        $resolver->setDefaults(array(
            'em'       => function (Options $options) {
                return $this->emFactory->getManager($this->objectFactory, $options['project']);
            },
            'ajax' => false,
            'allow_add' => false,
            'allow_select' => false,
        ));

        $resolver->setRequired(array('project'));
    }

    public function getName()
    {
        return 'pum_object_entity';
    }
}

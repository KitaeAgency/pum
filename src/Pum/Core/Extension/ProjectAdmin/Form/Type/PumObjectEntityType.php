<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Type;

use Pum\Core\Extension\EmFactory\EmFactory;
use Pum\Core\ObjectFactory;
use Pum\Bundle\CoreBundle\PumContext;
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
    protected $pumContext;

    public function __construct(ObjectFactory $objectFactory, EmFactory $emFactory, PumContext $pumContext)
    {
        $this->objectFactory = $objectFactory;
        $this->emFactory     = $emFactory;
        $this->pumContext    = $pumContext;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['allow_add']    = $options['allow_add'];
        $view->vars['allow_select'] = $options['allow_select'];
        $view->vars['class']        = $options['class'];
        $view->vars['ajax']         = $options['ajax'];
        $view->vars['ajaxsearch']   = false;

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
            'em' => function (Options $options) {
                return $this->emFactory->getManager($this->objectFactory, $options['project']);
            },
            'class' => function (Options $options) {
                $project = $options['project'] instanceof Project ? $project->getName() : $options['project'];

                return $this->objectFactory->getClassName($project, $options['pum_object']);
            },
            'by_reference' => function (Options $options) {
                return !$options['multiple'];
            },
            'empty_data' => function (Options $options, $v) {
                if ($v) {
                    return $v;
                }

                if ($options['multiple']) {
                    return '';
                }

                return;
            },
            'pum_object'   => null,
            'ajax'         => true,
            'allow_add'    => false,
            'allow_select' => false,
            'project'      => function(Options $options) {
                return $this->pumContext->getProjectName();
            }
        ));
    }

    public function getName()
    {
        return 'pum_object_entity';
    }
}

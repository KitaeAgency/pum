<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type extension for a help message
 *
 */
class HelpMessageTypeExtension extends AbstractTypeExtension
{

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $help = null;

        if (null != ($parent = $form->getParent())) {
            if (null != ($label = $form->getConfig()->getOption('label'))) {
                if (null != ($formView = $parent->getConfig()->getOption('form_view'))) {
                    if ($formView->hasField($label)) {
                        $help = $formView->getField($label)->getHelp();
                    }
                }
            }
        }

        $view->vars['help'] = $help;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'help' => null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'form';
    }
}

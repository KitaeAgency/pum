<?php

namespace Pum\Bundle\ProjectAdminBundle\Form\TypeExtension;

use Pum\Core\ObjectFactory;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Form type extension for a help message
 *
 */
class HelpMessageTypeExtension extends AbstractTypeExtension
{
    private $objectFactory;

    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $help = null;

        if (null != ($parent = $form->getParent())) {
            if (null != ($label = $form->getConfig()->getOption('label'))) {
                if (null != ($formView = $parent->getConfig()->getOption('form_view'))) {
                    if (is_string($formView)) {
                        list($project, $object) = $this->objectFactory->getProjectAndObjectFromClass(get_class($parent->getData()));
                        $formView = $object->getFormView($formView);
                    }
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

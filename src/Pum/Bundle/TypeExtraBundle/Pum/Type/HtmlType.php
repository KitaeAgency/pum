<?php

namespace Pum\Bundle\TypeExtraBundle\Pum\Type;

use Doctrine\ORM\Mapping\ClassMetadata as DoctrineMetadata;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\AbstractType;
use Pum\Core\Context\FieldBuildContext;
use Pum\Core\Context\FieldContext;
use Pum\Core\Definition\View\FormViewField;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidationMetadata;

class HtmlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'is_inline'     => false, // block (<p>, <div>....) --- inline (<br />)
            'label'         => null,
            'placeholder'   => null,
            'required'      => false
        ));
    }

    public function buildField(FieldBuildContext $context)
    {
        $cb    = $context->getClassBuilder();
        $camel = $context->getField()->getCamelCaseName();

        $cb->createProperty($camel);
        $cb->addGetMethod($camel);
        $cb->addSetMethod($camel);
    }

    /**
     * {@inheritdoc}
     */
    public function mapDoctrineField(FieldContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => $context->getField()->getLowercaseName(),
            'fieldName' => $context->getField()->getCamelCaseName(),
            'type'      => 'text',
            'nullable'  => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'html';
    }

    public function buildForm(FieldContext $context, FormBuilderInterface $form, FormViewField $formViewField)
    {
        $name = $context->getField()->getCamelCaseName();

        $toolbar = $this->getDefaultWysiwygConfiguration($context->getOption('is_inline'));

        $ckeditorConfig = array(
            'toolbar'      => $toolbar,
            'customConfig' => '',
        );

        $configJson = $formViewField->getOption('config_json');
        if ($configJson = json_decode($configJson, true)) {
            $ckeditorConfig = array_merge($ckeditorConfig, $configJson);
        }

        $options = array(
            'attr' => array(
                'data-ckeditor' => json_encode($ckeditorConfig),
                'placeholder'   => $formViewField->getPlaceholder()
            ),
            'label'    => $formViewField->getLabel(),
            'required' => $context->getOption('required'),
        );

        $form->add($name, 'textarea', $options);
    }

    /**
     * {@inheritdoc}
     */
    public function buildFormViewOptions(FormBuilderInterface $builder, FormViewField $formViewField)
    {
        $builder
            ->add('config_json', 'textarea', array(
                'attr' => array(
                    'placeholder' => 'pa.form.formview.fields.entry.options.config.json.placeholder'
                )
            ))
        ;
    }

    public function mapValidation(FieldContext $context, ValidationMetadata $metadata)
    {
        // :-)
    }

    /**
     * {@inheritdoc}
     */
    public function buildOptionsForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('is_inline', 'checkbox', array('required' => false))
            ->add('required', 'checkbox', array('required' => false))
        ;
    }
    
    private function getDefaultWysiwygConfiguration($is_inline = false)
    {
        if (false === $is_inline) {
            $defaultConfiguration = array(
                array(
                    'name'   => 'document',
                    'groups' => array(
                        'mode',
                        'document',
                        'doctools'
                    ),
                    'items' => array(
                        'Source'
                    )
                ),
                array(
                    'name'   => 'clipboard',
                    'groups' => array(
                        'clipboard',
                        'undo'
                    ),
                    'items' => array(
                        'Cut',
                        'Copy',
                        'Paste',
                        'PasteText',
                        'PasteFromWord',
                        '-',
                        'Undo',
                        'Redo',
                    )
                ),
                array(
                    'name'   => 'editing',
                    'groups' => array(
                        'find',
                        'selection',
                        'spellchecker'
                    ),
                    'items' => array(
                        'Find',
                        'Replace',
                        '-',
                        'Scayt'
                    )
                ),
                array(
                    'name'   => 'tools',
                    'items' => array(
                        'Maximize',
                        'ShowBlocks'
                    )
                ),
                array(
                    'name'   => 'paragraphA',
                    'groups' => array(
                        'indent',
                        'align',
                        'bidi'
                    ),
                    'items' => array(
                        'Outdent',
                        'Indent',
                        '-',
                        'Blockquote',
                        '-',
                        'JustifyLeft',
                        'JustifyCenter',
                        'JustifyRight',
                        'JustifyBlock'
                    )
                ),
                '/',
                array(
                    'name'   => 'basicstyles',
                    'groups' => array(
                        'basicstyles',
                        'cleanup'
                    ),
                    'items' => array(
                        'Bold',
                        'Italic',
                        'Underline',
                        'Strike',
                        'Subscript',
                        'Superscript',
                        '-',
                        'RemoveFormat'
                    )
                ),
                array(
                    'name'   => 'paragraphB',
                    'groups' => array(
                        'list',
                        'blocks'
                    ),
                    'items' => array(
                        'NumberedList',
                        'BulletedList',
                        '-',
                        'Blockquote'
                    )
                ),
                array(
                    'name'   => 'links',
                    'items' => array(
                        'Link',
                        'Unlink',
                        'Anchor'
                    )
                ),
                array(
                    'name'   => 'oembed',
                    'items' => array(
                        'Image',
                        'Table',
                        'HorizontalRule',
                        'SpecialChar',
                        'Iframe',
                        'Googledocs',
                        'gg'
                    )
                ),
                array(
                    'name'   => 'styles',
                    'items' => array(
                        'FontSize'
                    )
                ),
                array(
                    'name'   => 'colors',
                    'items' => array(
                        'TextColor',
                        'BGColor'
                    )
                )
            );
        }
        else {
            $defaultConfiguration = array(
                array(
                    'Bold',
                    'Italic',
                    'Link'
                )
            );
        }
        
        return $defaultConfiguration;
    }
}

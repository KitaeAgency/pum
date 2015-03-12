<?php

namespace Pum\Bundle\WoodworkBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Extends form and give opportunity to pass block prefixes as options.
 */
class WoodworkConfigTypeExtension extends AbstractTypeExtension
{
    protected $rootPath;

    protected $ww_logo = null;
    protected $ww_logo_small = null;

    public function __construct($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->get('tabs')
            ->add(
                $builder->create('woodwork', 'pum_tab')
                    ->add('ww_name', 'text', array('required' => false))
                    ->add('ww_logo', 'file', array('required' => false))
                    ->add('ww_logo_remove', 'checkbox', array('data' => false, 'required' => false))
                    ->add('ww_logo_small', 'file', array('required' => false))
                    ->add('ww_logo_small_remove', 'checkbox', array('data' => false, 'required' => false))
                    ->add('ww_logo_favicon', 'file', array('required' => false))
                    ->add('ww_logo_favicon_remove', 'checkbox', array('data' => false, 'required' => false))
                    ->add('ww_hide_pum_powered_login', 'checkbox', array('data' => false, 'required' => false))
                    ->add('ww_reverse_seo_object_template_handler', 'checkbox', array('required' => false))
                    ->add('ww_show_export_import_button', 'checkbox', array('required' => false))
                    ->add('ww_show_clone_button', 'checkbox', array('required' => false))
            );

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $data = $event->getData();

            $files = array('ww_logo', 'ww_logo_small', 'ww_logo_favicon');
            foreach ($files as $file) {
                if (isset($data[$file]) && !empty($data[$file])) {
                    $this->{$file} = $data[$file];
                    unset($data[$file]);
                } else {
                    $event->getForm()->get('tabs')->get('woodwork')->remove($file . '_remove');
                    unset($data[$file . '_remove']);
                }
            }

            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            $files = array('ww_logo', 'ww_logo_small', 'ww_logo_favicon');
            foreach ($files as $file) {
                if (isset($data[$file]) && $data[$file] instanceof UploadedFile) {
                    $upload = $data[$file];
                    unset($data[$file]);

                    // Remove previous file
                    if (!empty($this->{$file})) {
                        $this->deleteFile($this->getUploadRootDir() . $this->{$file});
                    }

                    if ($upload->move($this->getUploadRootDir() . $this->getUploadDir(), $upload->getClientOriginalName())) {
                        $data[$file] = $this->getUploadDir() . $upload->getClientOriginalName();
                    }
                } else if ($data[$file] == null && !empty($this->{$file})) {
                    $data[$file] = $this->{$file};
                }

                // Remove actual file
                if (isset($data[$file . '_remove'])) {
                    if ($data[$file . '_remove'] === true && !empty($this->{$file})) {
                        $this->deleteFile($this->getUploadRootDir() . $this->{$file});
                        $data[$file] = null;
                    }
                    unset($data[$file . '_remove']);
                }
            }

            $event->setData($data);
        });
    }

    protected function deleteFile($path)
    {
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }
    }

    protected function getUploadRootDir()
    {
        return $this->rootPath . '/../web';
    }

    protected function getUploadDir()
    {
        return '/medias/pum/';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'pum_form'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'pum_config';
    }
}

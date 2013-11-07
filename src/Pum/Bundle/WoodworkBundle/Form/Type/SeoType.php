<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Relation\Relation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Finder\Finder;

class SeoType extends AbstractType
{
    static protected $templatesFolder;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($options) {
            $builder          = $event->getForm();
            $objectDefinition = $event->getData();

            if ($options['formType'] !== 'template') {
                $builder
                    ->add('seoOrder', 'number', array(
                        'label' => $objectDefinition->getName(),
                        'attr' => array(
                            'data-sequence' => 'single'
                        )
                    ))
                ;
            } else {
                $templates = self::getTemplatesFolders($options['rootDir'], $options['bundlesName']);
                $builder->add('seoTemplate', 'choice', array(
                    'label' => $objectDefinition->getName(),
                    'choices'     => array_combine($templates, $templates),
                    'empty_value' => 'Choose a template',
                ));
            }
           
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'  => 'Pum\Core\Definition\ObjectDefinition',
            'formType'    => null,
            'rootDir'     => null,
            'bundlesName' => null
        ));
    }

    public function getName()
    {
        return 'ww_seo';
    }

    public static function getTemplatesFolders($rootDir, $bundles)
    {
        if (null !== SeoType::$templatesFolder) {
            return SeoType::$templatesFolder;
        }

        $templates = array();
        $folders   = array();
        foreach ($bundles as $bundle => $class) {
            if (is_dir($dir = $rootDir.'/Resources/'.$bundle.'/pum_views')) {
                $folders[] = $dir;
            }

            $reflection = new \ReflectionClass($class);
            if (is_dir($dir = dirname($reflection->getFilename()).'/Resources/pum_views')) {
                $folders[] = $dir;
            }
        }

        $finder = new Finder();
        $finder->in($folders);
        $finder->files()->name('*.twig');
        $finder->files()->contains('{# root #}');

        foreach ($finder as $file) {
            $templates[] = 'pum://'.str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());
        }
        
        return SeoType::$templatesFolder = $templates;
    }
}
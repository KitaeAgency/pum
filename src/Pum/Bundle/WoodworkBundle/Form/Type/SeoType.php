<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Relation\Relation;
use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Finder\Finder;

class SeoType extends AbstractType
{
    protected $context;
    protected $templatesFolder;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

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
                $templates = $this->getTemplatesFolders();
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
            'translation_domain' => 'pum_form'
        ));
    }

    public function getName()
    {
        return 'ww_seo';
    }

    protected function getTemplatesFolders()
    {
        if (null !== $this->templatesFolder) {
            return $this->templatesFolder;
        }

        $rootDir = $this->context->getContainer()->getParameter('kernel.root_dir');
        $bundles = $this->context->getContainer()->getParameter('kernel.bundles');

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

        return $this->templatesFolder = $templates;
    }
}
<?php

namespace Pum\Bundle\WoodworkBundle\Form\Type;

use Pum\Core\Definition\ObjectDefinition;
use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ObjectChoiceList;
use Symfony\Component\Finder\Finder;

class ObjectDefinitionSeoType extends AbstractType
{
    protected $context;
    protected $templatesFolder;

    public function __construct(PumContext $context)
    {
        $this->context = $context;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // here, we're not able to use form events because form inherits data.
        // That's a limitation, because of:
        // https://github.com/symfony/symfony/issues/8607

        $fields = array();
        $objectDefinition = $options['objectDefinition'];
        foreach ($objectDefinition->getFields() as $field) {
            if ($field->getType() != 'relation') {
                $fields[] = $field;
            }
        }

        $builder
            ->add('seoEnabled', 'checkbox', array(
                'required'    => false,
            ))
            ->add('seoField', 'entity', array(
                    'class'       => 'Pum\Core\Definition\FieldDefinition',
                    'choice_list' => new ObjectChoiceList($fields, 'name', array(), 'object.name', 'name'),
                    'required'    => false,
                ))
            /*->add('seoField', 'entity', array('class' => 'Pum\Core\Definition\FieldDefinition', 'property' => 'name', 'group_by' => 'object.name'))*/
        ;

        $templates = $this->getTemplatesFolders();

        $builder->add('seoTemplate', 'choice', array(
            'choices'     => array_combine($templates, $templates),
            'empty_value' => true
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'inherit_data' => true,
            'rootDir'      => null,
            'bundlesName'  => null
        ));

        $resolver->setRequired(array('objectDefinition'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ww_object_definition_seo';
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

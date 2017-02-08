<?php

namespace Pum\Core\Extension\Routing\Behavior;

use Pum\Core\Behavior;
use Pum\Core\BehaviorInterface;
use Pum\Core\Context\ObjectBuildContext;
use Pum\Core\Context\ObjectContext;
use Doctrine\ORM\Mapping\ClassMetadata;
use Pum\Core\Extension\Util\Namer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class SeoBehavior extends Behavior
{
    const SLUG_FIELD_NAME     = 'object_slug';
    const TEMPLATE_FIELD_NAME = 'object_template';

    const HAS_VIEW_TAB          = true;
    const HAS_EDIT_TAB          = true;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->securityContext->isGranted('ROLE_PA_ROUTING');
    }

    /**
     * @param  FormBuilderInterface $builder
     * @param  array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->isEnabled()) {
            $builder->add(
                $builder->create('routing', 'section')
                ->add('seo', 'ww_object_definition_seo', array(
                    'label' => ' ',
                    'attr' => array(
                        'class' => 'pum-scheme-panel-amethyst'
                    ),
                    'objectDefinition' => $builder->getData()
                ))
            );
        }
    }

    public function getProjectAdminForm()
    {
        return 'pum_object_routing';
    }

    public static function getCamelCaseSlugField()
    {
        return Namer::toCamelCase(self::SLUG_FIELD_NAME);
    }

    public static function getCamelCaseTemplateField()
    {
        return Namer::toCamelCase(self::TEMPLATE_FIELD_NAME);
    }

    public function mapDoctrineObject(ObjectContext $context, ClassMetadata $metadata)
    {
        $metadata->mapField(array(
            'columnName' => Namer::toLowercase(self::SLUG_FIELD_NAME),
            'fieldName' => Namer::toCamelCase(self::SLUG_FIELD_NAME),
            'type'      => 'string',
            'length'    => 255,
            'unique'    => true,
            'nullable'  => true
        ));

        $metadata->mapField(array(
            'columnName' => Namer::toLowercase(self::TEMPLATE_FIELD_NAME),
            'fieldName' => Namer::toCamelCase(self::TEMPLATE_FIELD_NAME),
            'type'      => 'string',
            'length'    => 255,
            'unique'    => false,
            'nullable'  => true
        ));
    }

    public function buildObject(ObjectBuildContext $context)
    {
        $cb = $context->getClassBuilder();
        $field = $context->getObject()->getSeoField();
        if (!$field) {
            return; // misconfigured
        }

        $getter        = 'get'.ucfirst($field->getCamelCaseName());
        $tplExport     = var_export($context->getObject()->getSeoTemplate(), true);
        $seoOrder      = var_export($context->getObject()->getSeoOrder(), true);
        $slugField     = Namer::toCamelCase(self::SLUG_FIELD_NAME);
        $templateField = Namer::toCamelCase(self::TEMPLATE_FIELD_NAME);

        $cb->addImplements('Pum\Core\Extension\Routing\RoutableInterface');

        $cb->createProperty($slugField);
        $cb->addGetMethod($slugField);
        $cb->addSetMethod($slugField);

        $cb->createProperty($templateField);
        $cb->addGetMethod($templateField);
        $cb->addSetMethod($templateField);

        $cb->createMethod('getSeoKey', null, '
            if ($this->get'.ucfirst($slugField).'()) {
                return \Pum\Core\Extension\Util\Namer::toSlug($this->get'.ucfirst($slugField).'());
            }

            return \Pum\Core\Extension\Util\Namer::toSlug($this->'.$getter.'());
        ');
        $cb->createMethod('getSeoTemplate', null, '
            if ($this->get'.ucfirst($templateField).'()) {
                return $this->get'.ucfirst($templateField).'();
            }

            return '.$tplExport.';
        ');
        $cb->createMethod('getSeoOrder', null, 'return '.$seoOrder.';');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'seo';
    }
}

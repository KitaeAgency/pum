<?php

namespace Pum\Bundle\TypeExtraBundle\Extension\View;

use Doctrine\Common\Util\ClassUtils;
use Pum\Core\ObjectFactory;
use Pum\Bundle\TypeExtraBundle\Media\StorageInterface;
use Pum\Bundle\TypeExtraBundle\Model\Media;

class View
{
    const MEDIA_TYPE   = 'media';
    const DEFAULT_VIEW = 'default';
    const PATH_PREFIX  = 'pum://';
    const PROJECT_PATH = 'project/';
    const FIELD_PATH   = 'field/';

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

    /**
     * @var array
     */
    protected $cache;

    /**
     * @param Twig_Environment $twig twig instance with "pum://" loader already injected
     */
    public function __construct(ObjectFactory $objectFactory, StorageInterface $storage, \Twig_Environment $twig)
    {
        $this->objectFactory = $objectFactory;
        $this->storage       = $storage;
        $this->twig          = $twig;
        $this->cache         = array();
    }

    public function getMediaUrl(Media $media, $width = 0, $height = 0, $options = array())
    {
        return $this->storage->getWebPath($media, $width, $height, $options);
    }

    public function getMediaUrlById($id, $isImage, $width = 0, $height = 0, $options = array())
    {
        return $this->storage->getWebPathFromId($id, $isImage, $width, $height);
    }

    /**
     * Renders mdia field of a given object.
     *
     * @return string result
     */
    public function renderPumMedia($object, $mediaFieldName, $view = null, array $vars = array())
    {
        if (null === $view) {
            $view = self::DEFAULT_VIEW;
        }

        list($project, $objectDefinition) = $this->objectFactory->getProjectAndObjectFromClass(ClassUtils::getClass($object));

        $field  = $objectDefinition->getField($mediaFieldName);
        $getter = 'get'.ucfirst($field->getCamelCaseName());
        $type   = $field->getType();

        if ($type !== self::MEDIA_TYPE) {
            throw new \RuntimeException(sprintf('Field %s is not a media type', $type));
        }

        /* Vars for templates */
        $vars  = array_merge(array(
            'entityID'   => $object->getId(),
            'identifier' => $field->getLowercaseName(),
            'value'      => $object->$getter(),
            'storage'    => $this->storage
        ), $vars);

        /* Templates Priority */
        $templates = array_unique(array(
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().'/'.self::FIELD_PATH.$type.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::PROJECT_PATH.$project->getLowercaseName().'/'.self::FIELD_PATH.$type.'/'.self::DEFAULT_VIEW.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.$view.'.html.twig',
            self::PATH_PREFIX.self::FIELD_PATH.$type.'/'.self::DEFAULT_VIEW.'.html.twig'
        ));

        /* Template cache */
        if (isset($this->cache['field_'.$type.'_'.$view])) {
            return $this->twig->loadTemplate($this->cache['field_'.$type.'_'.$view])->render($vars);
        }

        /* Search templates by priorty */
        foreach ($templates as $template) {
            try {
                $tpl = $this->twig->loadTemplate($template);
                $this->cache['field_'.$type.'_'.$view] = $template;
            } catch (\Twig_Error_Loader $e) {
                continue;
            }

            return $tpl->render($vars);
        }

        throw new \RuntimeException(sprintf('No field template "%s" found in resources: %s', $type.'/'.$view, implode(', ', $templates)));
    }
}

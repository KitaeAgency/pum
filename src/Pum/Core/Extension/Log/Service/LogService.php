<?php

namespace Pum\Core\Extension\Log\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Pum\Bundle\CoreBundle\Entity\Log;
use Pum\Bundle\CoreBundle\Entity\LogTag;
use Pum\Core\Extension\Log\LoggableEntity;
use Pum\Core\Extension\Log\LoggablePumEntity;

class LogService
{
    /**
     * @var Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    protected $container;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection $loggableEntities
     */
    protected $loggableEntities;

    /**
     * @var boolean $enabled;
     */
    protected $enabled;

    /**
     * @var Doctrine\Common\Collections\ArrayCollection $disabledOrigins
     */
    protected $disabledOrigins;

    public function __construct(ContainerInterface $container)
    {
        // Inject container to avoid circular reference when trying to access em or user.
        $this->container = $container;
        $this->loggableEntities = new ArrayCollection();

        $this->enabled = true;
        $this->disabledOrigins = new ArrayCollection();
    }

    public function addWoodworkLoggableEntity()
    {
        // Register FieldDefinition entity
        $this->addLoggableEntity(new LoggableEntity(
            'Pum\Core\Definition\FieldDefinition',
            'ww_object_definition_edit',
            array(
                'beamName' => 'this.getObject().getBeam().getName()',
                'name' => 'this.getObject().getName()'
            ),
            Log::ORIGIN_WOODWORK
        ));

        // Register ObjectDefinition entity
        $this->addLoggableEntity(new LoggableEntity(
            'Pum\Core\Definition\ObjectDefinition',
            'ww_beam_edit',
            array(
                'beamName' => 'this.getBeam().getName()'
            ),
            Log::ORIGIN_WOODWORK
        ));

        // Register Beam entity
        $this->addLoggableEntity(new LoggableEntity(
            'Pum\Core\Definition\Beam',
            'ww_beam_list',
            array(),
            Log::ORIGIN_WOODWORK
        ));
    }

    public function addProjectAdminLoggableEntity()
    {
        // Register Pum entities
        $this->addLoggableEntity(new LoggablePumEntity(
            'pa_object_list',
            array(
                '_project' => 'PUM_PROJECT',
                'beamName' => 'PUM_BEAM',
                'name' => 'PUM_OBJECT',
            ),
            Log::ORIGIN_PROJECT_ADMIN
        ));
    }

    public function addLoggableEntity($loggableEntity)
    {
        if ($loggableEntity instanceof LoggableEntity) {
            $this->loggableEntities->add($loggableEntity);
        } elseif (is_array($loggableEntity) && isset($loggableEntity['object'])) {
            $object = $loggableEntity['object'];
            $origin = Log::ORIGIN_NONE;
            $route = null;
            $parameters = array();

            if (isset($loggableEntity['origin'])) {
                $origin = $loggableEntity['origin'];
            }

            if (isset($loggableEntity['route']) && isset($loggableEntity['route']['name'])) {
                $route = $loggableEntity['route']['name'];

                if (isset($loggableEntity['route']['parameters']) && is_array($loggableEntity['route']['parameters'])) {
                    $parameters = $loggableEntity['route']['parameters'];
                }
            }

            $loggableEntity = new LoggableEntity($object, $origin, $route, $parameters);
            $this->loggableEntities->add($loggableEntity);
        }
    }

    protected function getLoggableEntity($class, $event)
    {
        foreach ($this->loggableEntities as $loggableEntity) {
            if ($loggableEntity->match($class, $event)) {
                return $loggableEntity;
            }
        }

        return null;
    }

    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function addDisabledOrigin($disabledOrigin)
    {
        $this->disabledOrigins[] = $disabledOrigin;
    }

    public function removeDisabledOrigin($disabledOrigin)
    {
        $this->disabledOrigins->removeElement($disabledOrigin);
    }

    public function getDisabledOrigin()
    {
        return $this->disabledOrigins;
    }

    public function create($entity, $event = Log::EVENT_NONE, array $options = array())
    {
        if ($entity instanceof Log) {
            // Don't log the log listerner
            return false;
        }

        $loggableEntity = $this->getLoggableEntity($entity, $event);
        if (!$loggableEntity) {
            return false;
        }

        if ($this->getDisabledOrigin()->contains($loggableEntity->getOrigin())) {
            return  false;
        }

        $router = $this->container->get('router');

        $log = new Log();
        $log->setEvent($event);
        $log->setCreated(new \DateTime());

        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user) {
            $log->setUser($user);
        }

        $log->setOrigin($loggableEntity->getOrigin());
        $log->setProject($loggableEntity->getProject());
        $log->setUrl($router->generate($loggableEntity->getRoute(), $loggableEntity->getParameters()));

        if (isset($options['description'])) {
            $log->setDescriptions($options['description']);
            unset($options['description']);
        }

        if (isset($options['em'])) {
            $options['changes'] = $this->formatChanges($entity, $options['em']);
            unset($options['em']);
        }

        if (isset($options['tags']) && is_array($options['tags'])) {
            $em = $this->container->get('doctrine.orm.entity_manager');

            $tagRepository = $em->getRepository('Pum\Bundle\CoreBundle\Entity\LogTag');
            $tags = new ArrayCollection($tagRepository->findBy(array('name' => $options['tags'])));

            foreach ($options['tags'] as $tagName) {
                $tag = $tags->filter(function($element) use ($tagName) {
                    return $element->getName() == $tagName;
                });

                if (!$tag->isEmpty()) {
                    $tag = $tag->first();
                } else {
                    $tag = new LogTag();
                    $tag->setName($tagName);
                }

                $log->addTag($tag);
            }
        }

        if (!empty($options)) {
            $log->setOptions($options);
        }

        return $log;
    }

    private function formatChanges($entity, EntityManager $em)
    {
        $uow = $em->getUnitOfWork();
        $changes = $uow->getEntityChangeSet($entity);

        if (is_array($changes) && !empty($changes)) {
            foreach ($changes as $k => $changeValues) {
                if (is_array($changeValues)) {
                    foreach ($changeValues as $l => $value) {
                        if (is_object($value) && method_exists($value, 'getId')) {
                            $changes[$k][$l] = $value->getId();
                        }
                    }
                }
            }

            return $changes;
        }

        return null;
    }
}

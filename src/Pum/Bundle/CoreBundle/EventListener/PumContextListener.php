<?php

namespace Pum\Bundle\CoreBundle\EventListener;

use Pum\Bundle\CoreBundle\PumContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class PumContextListener implements EventSubscriberInterface
{
    /**
     * @var PumContext
     */
    private $pumContext;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $attributeName;

    public function __construct(PumContext $pumContext, UrlGeneratorInterface $urlGenerator, $attributeName = '_project')
    {
        $this->pumContext   = $pumContext;
        $this->urlGenerator = $urlGenerator;
        $this->attributeName = $attributeName;
    }

    public function onRequest(KernelEvent $event)
    {
        $request     = $event->getRequest();
        $projectName = $request->attributes->get($this->attributeName);

        if (null !== $projectName) {
            $this->pumContext->setProjectName($projectName);
            $this->urlGenerator->getContext()->setParameter($this->attributeName, $projectName);
        }
    }

    public function onTerminate(PostResponseEvent $event)
    {
        $this->pumContext->removeProjectName();
        $this->urlGenerator->getContext()->setParameter($this->attributeName, null);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onRequest', 31),
            KernelEvents::TERMINATE => 'onTerminate',
        );
    }
}

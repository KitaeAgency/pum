<?php

namespace Pum\Core\Extension\EmFactory\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Pum\Core\Type\Factory\TypeFactoryInterface;

class ObjectTypeInjecter implements EventSubscriber
{
    protected $typeFactory;

    public function __construct(TypeFactoryInterface $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function getSubscribedEvents()
    {
        return array('postLoad');
    }

    public function postLoad(EventArgs $event)
    {
        $event->getEntity()->__pum__initialize($this->typeFactory);
    }
}

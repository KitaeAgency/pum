<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Core\Event\ProjectBeamEvent;
use Pum\Core\Event\ProjectEvent;
use Pum\Core\Events;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;
use Doctrine\Common\Collections\Criteria;

/**
 * A project.
 */
class Project extends EventObject
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection
     */
    protected $beams;

    /**
     * @var string
     */
    protected $contextMessages = '';

    /**
     * Constructor
     *
     * @param string $name
     */
    public function __construct($name = null)
    {
        $this->name   = $name;
        $this->beams = new ArrayCollection();
        $this->raise(Events::PROJECT_CREATE, new ProjectEvent($this));
    }

    /**
     * @param string $name
     * @return Project
     */
    public static function create($name = null)
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getLowercaseName()
    {
        return Namer::toLowerCase($this->name);
    }

    /**
     * @param string $name
     * @return Project
     */
    public function setName($name)
    {
        if ($name !== $this->name) {
            $this->raiseOnce(Events::PROJECT_UPDATE, new ProjectEvent($this));
        }

        $this->name = $name;

        return $this;
    }

    /**
     * @param Beam $beam
     * @return Project
     */
    public function addBeam(Beam $beam)
    {
        if (!$this->beams->contains($beam)) {
            $this->raise(Events::PROJECT_BEAM_ADDED, new ProjectBeamEvent($this, $beam));
            $this->getBeams()->add($beam);
            $beam->getProjects()->add($this);
        }

        return $this;
    }

    /**
     * @param Beam $beam
     * @return Project
     */
    public function removeBeam(Beam $beam)
    {
        if ($this->beams->contains($beam)) {
            $this->raise(Events::PROJECT_BEAM_REMOVED, new ProjectBeamEvent($this, $beam));
            $this->beams->removeElement($beam);
            $beam->getProjects()->removeElement($this);
        }

        return $this;
    }

    /**
     * @param Beam $beam
     * @return boolean
     */
    public function hasBeam(Beam $beam)
    {
        return $this->getBeams()->contains($beam);
    }

    /**
     * @return array
     */
    public function getBeams()
    {
        return $this->beams;
    }

    /**
     * @return ArrayCollection
     */
    public function getBeamsOrderBy($field = 'id', $order = Criteria::ASC)
    {
        return $this->beams;

        /* Need Doctrine 2.5 */
        /*$criteria = Criteria::create();

        $criteria->orderBy(array($field => $order));

        return $this->beams->matching($criteria);*/
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasObject($name)
    {
        try {
            $this->getObject($name);

            return true;
        } catch (DefinitionNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param $name
     * @throws DefinitionNotFoundException
     * @return ObjectDefinition
     *
     */
    public function getObject($name)
    {
        foreach ($this->getBeams() as $beam) {
            try {
                return $beam->getObject($name);
            } catch (DefinitionNotFoundException $e) {}
        }

        throw new DefinitionNotFoundException($name);
    }

    /**
     * @return array
     */
    public function getObjects()
    {
        $result = array();
        foreach ($this->getBeams() as $beam) {
            $result = array_merge($result, $beam->getObjects()->toArray());
        }

        return $result;
    }

    public function resetContextMessages()
    {
        $this->contextMessages = '';
    }

    public function getContextMessages()
    {
        return $this->contextMessages;
    }

    public function addContextError($message)
    {
        $this->addContextMessage('ERROR', $message);
    }

    public function addContextWarning($message)
    {
        $this->addContextMessage('WARNING', $message);
    }

    public function addContextInfo($message)
    {
        $this->addContextMessage('INFO', $message);
    }

    public function addContextDebug($message)
    {
        $this->addContextMessage('DEBUG', $message);
    }

    private function addContextMessage($level, $message)
    {
        $this->contextMessages .= '['.$level.'] '.date('Y-m-d H:i:s').' '.str_replace("\n", '\n', $message)."\n";
    }
}

<?php

namespace Pum\Core\Definition;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\Table;
use Pum\Core\Exception\DefinitionNotFoundException;
use Pum\Core\Extension\Util\Namer;

/**
 * A project.
 */
class Project
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
     * Constructor.
     */
    public function __construct($name = null)
    {
        $this->name   = $name;
        $this->beams = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Object
     */
    public static function create($name = null)
    {
        return new self($name);
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
     * @return Object
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Beam
     */
    public function addBeam(Beam $beam)
    {
        $this->getBeams()->add($beam);
        $beam->getProjects()->add($this);

        return $this;
    }

    /**
     * @return Beam
     */
    public function removeBeam(Beam $beam)
    {
        $this->getBeams()->removeElement($beam);
    }

    /**
     * @return Beam
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
     * @return boolean
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
     * @return ObjectDefinition
     *
     * @throws DefinitionNotFoundException
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
        $this->contextMessages .= '['.$level.'] '.str_replace("\n", '\n', $message)."\n";
    }
}

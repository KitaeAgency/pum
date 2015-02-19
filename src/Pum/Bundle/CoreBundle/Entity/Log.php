<?php

namespace Pum\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="LogRepository")
 * @ORM\Table(name="core_log")
 */
class Log
{

    const EVENT_NONE = 0;
    const EVENT_CREATE = 1;
    const EVENT_UPDATE = 2;
    const EVENT_DELETE = 3;

    const ORIGIN_NONE = null;
    const ORIGIN_WOODWORK = "woodwork";
    const ORIGIN_PROJECT_ADMIN = "project_admin";
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Pum\Bundle\AppBundle\Entity\User", inversedBy="logs")
     */
    protected $user;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $origin;

    /**
     * @ORM\Column(type="integer", nullable=false, options={"unsigned":true, "default":0})
     */
    protected $event;

    /**
     * @ORM\Column(type="array")
     */
    protected $options;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $project;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="LogTag", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="core_log_tag_relation",
     *      joinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="log_id", referencedColumnName="id")}
     *      )
     */
    protected $tags;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Log
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set origin
     *
     * @param string $origin
     * @return Log
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Log
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set user
     *
     * @param \Pum\Bundle\AppBundle\Entity\User $user
     * @return Log
     */
    public function setUser(\Pum\Bundle\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Pum\Bundle\AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add tags
     *
     * @param \Pum\Bundle\CoreBundle\Entity\LogTag $tags
     * @return Log
     */
    public function addTag(\Pum\Bundle\CoreBundle\Entity\LogTag $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    /**
     * Remove tags
     *
     * @param \Pum\Bundle\CoreBundle\Entity\LogTag $tags
     */
    public function removeTag(\Pum\Bundle\CoreBundle\Entity\LogTag $tags)
    {
        $this->tags->removeElement($tags);
    }

    /**
     * Get tags
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set event
     *
     * @param integer $event
     * @return Log
     */
    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return integer
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Log
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set options
     *
     * @param array $options
     * @return Log
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set project
     *
     * @param string $project
     * @return Log
     */
    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return string
     */
    public function getProject()
    {
        return $this->project;
    }
}

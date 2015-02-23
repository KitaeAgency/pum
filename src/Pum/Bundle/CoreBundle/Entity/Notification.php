<?php

namespace Pum\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\AppBundle\Entity\Group;

/**
 * @ORM\Entity(repositoryClass="NotificationRepository")
 * @ORM\Table(name="core_notification")
 */
class Notification
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $content_title;

    /**
     * @ORM\Column(type="text")
     */
    protected $content_body;

    /**
     * @ORM\Column(type="boolean", options={ "default"=false })
     */
    protected $email;

    /**
     * @ORM\Column(type="datetime", name="email_delayed", nullable=true)
     */
    protected $delayed;

    /**
     * @ORM\Column(type="datetime", name="email_sent", nullable=true)
     */
    protected $sent;

    /**
     * @ORM\ManyToMany(targetEntity="Pum\Bundle\AppBundle\Entity\Group")
     * @ORM\JoinTable(name="core_notification_group",
     *      joinColumns={@ORM\JoinColumn(name="notification_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Pum\Bundle\AppBundle\Entity\User")
     * @ORM\JoinTable(name="core_notification_user",
     *      joinColumns={@ORM\JoinColumn(name="notification_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    protected $users;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->groups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set content_title
     *
     * @param string $contentTitle
     * @return Notification
     */
    public function setContentTitle($contentTitle)
    {
        $this->content_title = $contentTitle;

        return $this;
    }

    /**
     * Get content_title
     *
     * @return string
     */
    public function getContentTitle()
    {
        return $this->content_title;
    }

    /**
     * Set content_body
     *
     * @param string $contentBody
     * @return Notification
     */
    public function setContentBody($contentBody)
    {
        $this->content_body = $contentBody;

        return $this;
    }

    /**
     * Get content_body
     *
     * @return string
     */
    public function getContentBody()
    {
        return $this->content_body;
    }

    /**
     * Set email
     *
     * @param boolean $email
     * @return Notification
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return boolean
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set delayed
     *
     * @param \DateTime $delayed
     * @return Notification
     */
    public function setDelayed($delayed)
    {
        $this->delayed = $delayed;

        return $this;
    }

    /**
     * Get delayed
     *
     * @return \DateTime
     */
    public function getDelayed()
    {
        return $this->delayed;
    }

    /**
     * Add groups
     *
     * @param \Pum\Bundle\AppBundle\Entity\Group $groups
     * @return Notification
     */
    public function addGroup(\Pum\Bundle\AppBundle\Entity\Group $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Remove groups
     *
     * @param \Pum\Bundle\AppBundle\Entity\Group $groups
     */
    public function removeGroup(\Pum\Bundle\AppBundle\Entity\Group $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add users
     *
     * @param \Pum\Bundle\AppBundle\Entity\User $users
     * @return Notification
     */
    public function addUser(\Pum\Bundle\AppBundle\Entity\User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param \Pum\Bundle\AppBundle\Entity\User $users
     */
    public function removeUser(\Pum\Bundle\AppBundle\Entity\User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set sent
     *
     * @param \DateTime $sent
     * @return Notification
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return \DateTime
     */
    public function getSent()
    {
        return $this->sent;
    }
}

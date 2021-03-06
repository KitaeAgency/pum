<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Bundle\CoreBundle\Entity\UserNotification;
use Pum\Core\Extension\Notification\Entity\UserNotificationInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Pum\Bundle\ProjectAdminBundle\Entity\CustomView;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="ww_user")
 * @UniqueEntity("username")
 */
class User extends UserNotification implements UserInterface, UserNotificationInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     * @Assert\Email()
     */
    protected $username;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $fullname;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected $salt;

    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="users")
     */
    protected $group;

    /**
     * @var UserPermission[]
     *
     * @ORM\OneToMany(targetEntity="UserPermission", mappedBy="user")
     */
    protected $advancedPermissions;

    /**
     * @var CustomView[]
     *
     * @ORM\OneToMany(targetEntity="Pum\Bundle\ProjectAdminBundle\Entity\CustomView", mappedBy="user")
     */
    protected $customViews;

    public function __construct($username = null)
    {
        $this->username    = $username;
        $this->customViews = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return User
     */
    public function setGroup(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return User
     */
    public function setPassword($password, EncoderFactoryInterface $factory)
    {
        $encoder = $factory->getEncoder($this);

        $this->salt     = md5(microtime().uniqid());
        $this->password = $encoder->encodePassword($password, $this->salt);

        return $this;
    }

    /**
     * @return User
     */
    public function setFullname($fullname)
    {
        $this->fullname = $fullname;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullname()
    {
        return $this->fullname;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isAdmin()
    {
        if (null !== $this->group) {
            if ($this->group->isAdmin()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return boolean
     */
    public function canEditPermissions(Group $group)
    {
        if ($group->isAdmin()) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $group = $this->getGroup();
        if ($group) {
            return array_unique($group->getPermissions());
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasWoodworkAccess()
    {
        $woodworkPermissions = Group::$woodworkPermissions;

        $group = $this->getGroup();
        foreach ($group->getPermissions() as $permission) {
            if (in_array($permission, $woodworkPermissions)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @param Permission[] $advancedPermissions
     */
    public function setAdvancedPermissions(array $advancedPermissions)
    {
        $this->advancedPermissions->clear();
        foreach ($advancedPermissions as $permission) {
            $this->advancedPermissions->add($permission);
        }
    }

    /**
     * @return Permission[]
     */
    public function getAdvancedPermissions()
    {
        return $this->advancedPermissions;
    }

    /**
     * @param Permission $advancedPermission
     */
    public function addAdvancedPermission(Permission $advancedPermission)
    {
        $this->advancedPermissions->add($advancedPermission);
    }

    /**
     * @param Permission $advancedPermission
     * @return bool Whether or not the element was successfully removed
     */
    public function removeAdvancedPermission(Permission $advancedPermission)
    {
        return $this->advancedPermissions->removeElement($advancedPermission);
    }

    /**
     * @param CustomView[] $customViews
     */
    public function setCustomViews(array $customViews)
    {
        $this->customViews->clear();
        foreach ($customViews as $customView) {
            $this->customViews->add($customView);
        }
    }

    /**
     * @return CustomView[]
     */
    public function getCustomViews()
    {
        return $this->customViews;
    }

    /**
     * @param CustomView $customView
     */
    public function addCustomView(CustomView $customView)
    {
        $this->customViews->add($customView);
    }

    /**
     * @param CustomView $customView
     * @return bool Whether or not the element was successfully removed
     */
    public function removeCustomView(CustomView $customView)
    {
        return $this->customViews->removeElement($customView);
    }

    /**
     * Create Password
     * @param  integer $length
     * @return string $password
     */
    public static function createPwd($length = 6)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $password = substr(str_shuffle($chars), 0, $length);

        return $password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->username;
    }

    /**
     * @var \DateTime
     */
    private $last_notification;


    /**
     * Set last_notification
     *
     * @param \DateTime $lastNotification
     * @return User
     */
    public function setLastNotification($lastNotification)
    {
        $this->last_notification = $lastNotification;

        return $this;
    }

    /**
     * Get last_notification
     *
     * @return \DateTime
     */
    public function getLastNotification()
    {
        return $this->last_notification;
    }
}

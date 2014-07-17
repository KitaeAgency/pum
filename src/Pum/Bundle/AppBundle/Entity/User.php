<?php

namespace Pum\Bundle\AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="ww_user")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=32)
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
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="ww_user_group")
     */
    protected $groups;

    public function __construct($username = null)
    {
        $this->username = $username;
        $this->groups = new ArrayCollection();
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
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return User
     */
    public function addGroup(Group $group)
    {
        $this->getGroups()->add($group);

        return $this;
    }

    /**
     * @return User
     */
    public function removeGroup(Group $group)
    {
        $this->getGroups()->removeElement($group);

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
     * {@inheritdoc}
     */
    public function getRoles()
    {
        $roles = array();
        foreach ($this->getGroups() as $group) {
            $roles = array_merge($group->getPermissions());
        }

        return array_unique($roles);
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
}

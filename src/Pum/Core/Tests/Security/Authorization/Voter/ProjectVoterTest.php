<?php

namespace Pum\Core\Tests\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\CoreBundle\Security\Authorization\Voter\ProjectVoter;
use Pum\Core\Cache\StaticCache;
use Pum\Core\Definition\Project;
use Pum\Core\ObjectFactory;
use Pum\Core\Schema\StaticSchema;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class ProjectVoterTest extends \PHPUnit_Framework_TestCase
{
    private $voter;
    private $anonymousToken;
    private $lostToken;
    private $freshToken;
    private $adminToken;
    private $managerToken;

    public function setUp()
    {
        $registry = $this->getMock('Pum\Core\BuilderRegistry\BuilderRegistryInterface');
        $schema = new StaticSchema();
        $cache = new StaticCache();
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $objectFactory = new ObjectFactory($registry, $schema, $cache, $eventDispatcher);

        $this->voter = new ProjectVoter($objectFactory);
        $project = new Project('FooProject');
        $this->anonymousToken = new AnonymousToken('key', 'user');

        //The lost user which has no group
        $this->lostToken = new UsernamePasswordToken(new User(), null, 'secured_area', array('ROLE_USER'));

        //The fresh user which has no permissions
        $userGroup = new Group('Users');
        $freshUser = new User();
        $freshUser->addGroup($userGroup);
        $this->freshToken = new UsernamePasswordToken($freshUser, null, 'secured_area', array('ROLE_USER'));

        //The admin user which has a global permission to all projects
        $adminGroup = new Group('Administrators');
        $adminGroup->setPermissions(array('ROLE_WW_PROJECTS'));
        $adminUser = new User();
        $adminUser->addGroup($adminGroup);
        $this->adminToken = new UsernamePasswordToken($adminUser, null, 'secured_area', array('ROLE_ADMIN'));

        //The manager user which has PUM_PROJECT_LIST permission on FooProject
        $managerGroup = new Group('Managers');
        $managerUser = new User();
        $managerUser->addGroup($managerGroup);
        $listPermission = new Permission();
        $listPermission
            ->setAttribute('PUM_PROJECT_LIST')
            ->setProject($project)
            ->setGroup($managerGroup)
        ;
        $managerGroup->addAdvancedPermission($listPermission);
        $this->managerToken = new UsernamePasswordToken($managerUser, null, 'secured_area', array('ROLE_USER'));

        $objectFactory->saveProject($project);
    }

    public function testVoterAbstainWhenSubjectIsNotAProject()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->freshToken, new \stdClass(), ['PUM_PROJECT_LIST']));
    }

    public function testVoterAbstainWhenAttributeIsNotRelated()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->freshToken, 'FooProject', ['FOOBAR']));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Only one attribute is allowed
     */
    public function testVoterThrowsExceptionWhenMoreThanOneAttributeIsGiven()
    {
        $this->voter->vote($this->freshToken, 'FooProject', ['PUM_PROJECT_LIST', 'PUM_PROJECT_VIEW']);
    }

    public function testVoterDenyWhenUserIsNotAuthenticated()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->anonymousToken, 'FooProject', ['PUM_PROJECT_LIST']));
    }

    public function testVoterDenyWhenUserHasNoGroup()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->lostToken, 'FooProject', ['PUM_PROJECT_LIST']));
    }

    public function testVoterDenyWhenUserDoesNotHavePermission()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->freshToken, 'FooProject', ['PUM_PROJECT_LIST']));
    }

    public function testVoterGrantsWhenUserIsAdmin()
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->adminToken, 'FooProject', ['PUM_PROJECT_LIST']));
    }

    public function testVoterGrantsWhenUserHasPermission()
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->managerToken, 'FooProject', ['PUM_PROJECT_LIST']));
    }

    public function testVoterDenyWhenUserDoesNotHaveGivenPermission()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->managerToken, 'FooProject', ['PUM_PROJECT_DELETE']));
    }
}

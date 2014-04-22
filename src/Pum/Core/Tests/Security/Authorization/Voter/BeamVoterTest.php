<?php

namespace Pum\Core\Tests\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\CoreBundle\Security\Authorization\Voter\BeamVoter;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\Project;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class BeamVoterTest extends \PHPUnit_Framework_TestCase
{
    private $voter;
    private $user;
    private $project;
    private $beam;
    private $anonymousToken;
    private $lostToken;
    private $freshToken;
    private $adminToken;
    private $managerToken;

    public function setUp()
    {
        $this->project = new Project('FooProject');

        $pumContext = $this->getMockBuilder('\\Pum\\Bundle\\CoreBundle\\PumContext')
            ->disableOriginalConstructor()
            ->getMock();
        $pumContext
            ->expects($this->any())
            ->method('getProject')
            ->will($this->returnValue($this->project))
        ;

        $this->voter = new BeamVoter($pumContext);
        $this->user = new User();

        $this->beam = new Beam('FooBeam');
        $this->project->addBeam($this->beam);

        $this->anonymousToken = new AnonymousToken('key', 'user');

        //The lost user which has no group
        $this->lostToken = new UsernamePasswordToken(new User(), null, 'secured_area', array('ROLE_USER'));

        //The fresh user which has no permissions
        $userGroup = new Group('Users');
        $freshUser = new User();
        $freshUser->addGroup($userGroup);
        $this->freshToken = new UsernamePasswordToken($freshUser, null, 'secured_area', array('ROLE_USER'));

        //The admin user which has a global permission to all beams
        $adminGroup = new Group('Administrators');
        $adminGroup->setPermissions(array('ROLE_WW_BEAMS'));
        $adminUser = new User();
        $adminUser->addGroup($adminGroup);
        $this->adminToken = new UsernamePasswordToken($adminUser, null, 'secured_area', array('ROLE_ADMIN'));

        //The manager user which has PUM_BEAM_LIST permission on FooBeam
        $managerGroup = new Group('Managers');
        $managerUser = new User();
        $managerUser->addGroup($managerGroup);
        $listPermission = new Permission();
        $listPermission
            ->setAttribute('PUM_BEAM_LIST')
            ->setProject($this->project)
            ->setBeam($this->beam)
            ->setGroup($managerGroup)
        ;
        $managerGroup->addAdvancedPermission($listPermission);
        $this->managerToken = new UsernamePasswordToken($managerUser, null, 'secured_area', array('ROLE_USER'));
    }

    public function testVoterAbstainWhenSubjectIsNotABeam()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->freshToken, new \stdClass(), ['PUM_BEAM_LIST']));
    }

    public function testVoterAbstainWhenAttributeIsNotRelated()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->freshToken, $this->beam, ['FOOBAR']));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Only one attribute is allowed
     */
    public function testVoterThrowsExceptionWhenMoreThanOneAttributeIsGiven()
    {
        $this->voter->vote($this->freshToken, $this->beam, ['PUM_BEAM_LIST', 'PUM_BEAM_VIEW']);
    }

    public function testVoterDenyWhenUserIsNotAuthenticated()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->anonymousToken, $this->beam, ['PUM_BEAM_LIST']));
    }

    public function testVoterDenyWhenUserHasNoGroup()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->lostToken, $this->beam, ['PUM_BEAM_LIST']));
    }

    public function testVoterDenyWhenUserDoesNotHavePermission()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->freshToken, $this->beam, ['PUM_BEAM_LIST']));
    }

    public function testVoterGrantsWhenUserIsAdmin()
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->adminToken, $this->beam, ['PUM_BEAM_LIST']));
    }

    public function testVoterGrantsWhenUserHasPermission()
    {
        $this->assertSame(VoterInterface::ACCESS_GRANTED, $this->voter->vote($this->managerToken, $this->beam, ['PUM_BEAM_LIST']));
    }
}

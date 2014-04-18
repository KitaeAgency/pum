<?php

namespace Pum\Core\Tests\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\CoreBundle\Security\Authorization\Voter\ProjectVoter;
use Pum\Core\Definition\Project;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class ProjectVoterTest extends \PHPUnit_Framework_TestCase
{
    private $voter;
    private $user;
    private $project;
    private $anonymousToken;
    private $userToken;

    public function setUp()
    {
        $this->voter = new ProjectVoter();
        $this->user = new User();
        $this->project = new Project();
        $this->anonymousToken = new AnonymousToken('key', 'user');
        $this->userToken = new UsernamePasswordToken(new User(), null, 'secured_area', array('ROLE_USER'));
    }

    public function testVoterAbstainWhenSubjectIsNotAProject()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->anonymousToken, new \stdClass(), ['PUM_PROJECT_LIST']));
    }

    public function testVoterAbstainWhenAttributeIsNotRelated()
    {
        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $this->voter->vote($this->anonymousToken, $this->project, ['FOOBAR']));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Only one attribute is allowed
     */
    public function testVoterThrowsExceptionWhenMoreThanOneAttributeIsGiven()
    {
        $this->voter->vote($this->anonymousToken, $this->project, ['PUM_PROJECT_LIST', 'PUM_PROJECT_VIEW']);
    }

    public function testVoterDenyWhenUserIsNotAuthenticated()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->anonymousToken, $this->project, ['PUM_PROJECT_LIST']));
    }

    public function testVoterDenyWhenUserDoesNotHaveGivenPermission()
    {
        $this->assertSame(VoterInterface::ACCESS_DENIED, $this->voter->vote($this->userToken, $this->project, ['PUM_PROJECT_LIST']));
    }
}

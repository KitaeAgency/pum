<?php

namespace Pum\Core\Tests\Security\Authorization\Voter;

use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Bundle\AppBundle\Entity\Permission;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\CoreBundle\Security\Authorization\Voter\ObjectVoter;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class ObjectVoterTest extends \PHPUnit_Framework_TestCase
{
    private $voter;
    private $object;
    private $anonymousToken;
    private $lostToken;
    private $freshToken;
    private $adminToken;
    private $tokenHasViewPermOnProject;
    private $tokenHasViewPermOnBeam;
    private $tokenHasViewPermOnObject;
    private $tokenHasEditPermOnInstance;
    private $tokenHasMasterPermOnObject;

    public function setUp()
    {
        $this->voter = new ObjectVoter();

        $project = new Project('FooProject');
        $beam = new Beam('FooBeam');
        $project->addBeam($beam);

        $this->object = new ObjectDefinition('FooObject');
        $beam->addObject($this->object);

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

        $group1 = new Group('HasViewPermissionOnProject');
        $user1 = new User();
        $user1->addGroup($group1);
        $perm1 = new Permission();
        $perm1
            ->setGroup($group1)
            ->setAttribute('PUM_OBJ_VIEW')
            ->setProject($project)
        ;
        $group1->addAdvancedPermission($perm1);
        $this->tokenHasViewPermOnProject = new UsernamePasswordToken($user1, null, 'secured_area', array('ROLE_USER'));

        $group2 = new Group('HasViewPermissionOnBeam');
        $user2 = new User();
        $user2->addGroup($group2);
        $perm2 = new Permission();
        $perm2
            ->setGroup($group2)
            ->setAttribute('PUM_OBJ_VIEW')
            ->setProject($project)
            ->setBeam($beam)
        ;
        $group2->addAdvancedPermission($perm2);
        $this->tokenHasViewPermOnBeam = new UsernamePasswordToken($user2, null, 'secured_area', array('ROLE_USER'));

        $group3 = new Group('HasViewPermissionOnObject');
        $user3 = new User();
        $user3->addGroup($group3);
        $perm3 = new Permission();
        $perm3
            ->setGroup($group3)
            ->setAttribute('PUM_OBJ_VIEW')
            ->setProject($project)
            ->setBeam($beam)
            ->setObject($this->object)
        ;
        $group3->addAdvancedPermission($perm3);
        $this->tokenHasViewPermOnObject = new UsernamePasswordToken($user3, null, 'secured_area', array('ROLE_USER'));

        $group4 = new Group('HasEditPermissionOnInstance');
        $user4 = new User();
        $user4->addGroup($group4);
        $perm4 = new Permission();
        $perm4
            ->setGroup($group4)
            ->setAttribute('PUM_OBJ_EDIT')
            ->setProject($project)
            ->setBeam($beam)
            ->setObject($this->object)
            ->setInstance(1)
        ;
        $group4->addAdvancedPermission($perm4);
        $this->tokenHasEditPermOnInstance = new UsernamePasswordToken($user4, null, 'secured_area', array('ROLE_USER'));

        $group5 = new Group('HasMasterPermissionOnObject');
        $user5 = new User();
        $user5->addGroup($group5);
        $perm5 = new Permission();
        $perm5
            ->setGroup($group5)
            ->setAttribute('PUM_OBJ_MASTER')
            ->setProject($project)
            ->setBeam($beam)
        ;
        $group5->addAdvancedPermission($perm5);
        $this->tokenHasMasterPermOnObject = new UsernamePasswordToken($user5, null, 'secured_area', array('ROLE_USER'));
    }

    public function testVoterAbstainWhenSubjectIsNotABeam()
    {
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->freshToken, new \stdClass(), ['PUM_OBJ_VIEW'])
        );
    }

    public function testVoterAbstainWhenAttributeIsNotRelated()
    {
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->freshToken, array('project' => 'FooProject'), ['FOOBAR'])
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Only one attribute is allowed
     */
    public function testVoterThrowsExceptionWhenMoreThanOneAttributeIsGiven()
    {
        $this->voter->vote($this->freshToken, array('project' => 'FooProject'), ['PUM_OBJ_VIEW', 'PUM_OBJ_EDIT']);
    }

    public function testVoterDenyWhenUserIsNotAuthenticated()
    {
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->anonymousToken, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );
    }

    public function testVoterDenyWhenUserHasNoGroup()
    {
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->lostToken, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );
    }

    public function testVoterDenyWhenUserDoesNotHavePermission()
    {
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->freshToken, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );
    }

    //User is admin but that does not give him the permission on the object
    public function testVoterDenyWhenUserIsAdmin()
    {
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->adminToken, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );
    }

    public function testVoterGrantsWhenUserHasHigherPermission()
    {
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasViewPermOnProject, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject'), ['PUM_OBJ_VIEW'])
        );
    }

    public function testVoterDenyWhenUserDoesNotHaveGivenPermission()
    {
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasViewPermOnProject, array('project' => 'FooProject'), ['PUM_OBJ_DELETE'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasViewPermOnBeam, array('project' => 'FooProject', 'beam' => 'FooBeam'), ['PUM_OBJ_DELETE'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasViewPermOnObject, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject'), ['PUM_OBJ_DELETE'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasEditPermOnInstance, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_DELETE'])
        );
    }

    public function testVoterDenyWhenUserDoesNotHaveHigherPermission()
    {
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasViewPermOnBeam, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasViewPermOnObject, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasEditPermOnInstance, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );
    }

    public function testVoterGrantsWhenUserHasPermissionOnInstance()
    {
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasEditPermOnInstance, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_EDIT'])
        );
    }

    public function testVoterGrantsWhenUserHasMasterPermission()
    {
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasMasterPermOnObject, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_VIEW'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasMasterPermOnObject, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_EDIT'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasMasterPermOnObject, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_DELETE'])
        );
    }

    public function testVoterGrantsViewWhenUserHasEditPermission()
    {
        //Grants View...
        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasEditPermOnInstance, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_VIEW'])
        );

        //...but not anything else
        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($this->tokenHasEditPermOnInstance, array('project' => 'FooProject', 'beam' => 'FooBeam', 'object' => 'FooObject', 'id' => 1), ['PUM_OBJ_DELETE'])
        );
    }
}

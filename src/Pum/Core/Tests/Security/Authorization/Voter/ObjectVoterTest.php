<?php

namespace Pum\Core\Tests\Security\Authorization\Voter;

use Pum\Core\Tests\Schema\DoctrineOrmSchemaTest;
use Pum\Bundle\AppBundle\Entity\Group;
use Pum\Bundle\AppBundle\Entity\GroupPermission;
use Pum\Bundle\AppBundle\Entity\User;
use Pum\Bundle\AppBundle\Entity\UserPermissionRepository;
use Pum\Bundle\CoreBundle\Security\Authorization\Voter\ObjectVoter;
use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder;
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
        $em = DoctrineOrmSchemaTest::createEntityManager('ObjectVoter' . mcrypt_create_iv(20));

        $project = new Project('FooProject');
        //$em->persist($project);

        $beam = new Beam('FooBeam');
        $beam->setIcon('icon');
        $beam->setColor('color');
        $project->addBeam($beam);
        //$em->persist($beam);

        $this->object = new ObjectDefinition('FooObject');
        $beam->addObject($this->object);
        //$em->persist($this->object);

        $encoderFactory = new EncoderFactory(array(
            'Pum\Bundle\AppBundle\Entity\User' => new PlaintextPasswordEncoder()
        ));

        $this->anonymousToken = new AnonymousToken('key', 'user');

        //The lost user which has no group
        $this->lostToken = new UsernamePasswordToken(new User(), null, 'secured_area', array('ROLE_USER'));

        //The fresh user which has no permissions
        $userGroup = new Group('Users');
        $freshUser = new User();
        $freshUser->setUsername('user@kitae.fr');
        $freshUser->setFullname('User');
        $freshUser->setPassword('password', $encoderFactory);
        $freshUser->setGroup($userGroup);
        $this->freshToken = new UsernamePasswordToken($freshUser, null, 'secured_area', array('ROLE_USER'));

        //The admin user which has a global permission to all beams
        $adminGroup = new Group('Administrators');
        $adminGroup->setPermissions(array('ROLE_WW_BEAMS'));
        $adminUser = new User();
        $adminUser->setUsername('admin@kitae.fr');
        $adminUser->setFullname('Admin');
        $adminUser->setPassword('password', $encoderFactory);
        $adminUser->setGroup($adminGroup);
        $this->adminToken = new UsernamePasswordToken($adminUser, null, 'secured_area', array('ROLE_ADMIN'));

        $group1 = new Group('HasViewPermissionOnProject');
        $user1 = new User();
        $user1->setUsername('user1@kitae.fr');
        $user1->setFullname('User 1');
        $user1->setPassword('password', $encoderFactory);
        $user1->setGroup($group1);
        $perm1 = new GroupPermission();
        $perm1
            ->setGroup($group1)
            ->setAttributes(array('PUM_OBJ_VIEW'))
            ->setProject($project)
        ;
        $group1->addAdvancedPermission($perm1);
        $this->tokenHasViewPermOnProject = new UsernamePasswordToken($user1, null, 'secured_area', array('ROLE_USER'));

        $group2 = new Group('HasViewPermissionOnBeam');
        $user2 = new User();
        $user2->setUsername('user2@kitae.fr');
        $user2->setFullname('User 2');
        $user2->setPassword('password', $encoderFactory);
        $user2->setGroup($group2);
        $perm2 = new GroupPermission();
        $perm2
            ->setGroup($group2)
            ->setAttributes(array('PUM_OBJ_VIEW'))
            ->setProject($project)
            ->setBeam($beam)
        ;
        $group2->addAdvancedPermission($perm2);
        $this->tokenHasViewPermOnBeam = new UsernamePasswordToken($user2, null, 'secured_area', array('ROLE_USER'));

        $group3 = new Group('HasViewPermissionOnObject');
        $user3 = new User();
        $user3->setUsername('user3@kitae.fr');
        $user3->setFullname('User 3');
        $user3->setPassword('password', $encoderFactory);
        $user3->setGroup($group3);
        $perm3 = new GroupPermission();
        $perm3
            ->setGroup($group3)
            ->setAttributes(array('PUM_OBJ_VIEW'))
            ->setProject($project)
            ->setBeam($beam)
            ->setObject($this->object)
        ;
        $group3->addAdvancedPermission($perm3);
        $this->tokenHasViewPermOnObject = new UsernamePasswordToken($user3, null, 'secured_area', array('ROLE_USER'));

        $group4 = new Group('HasEditPermissionOnInstance');
        $user4 = new User();
        $user4->setUsername('user4@kitae.fr');
        $user4->setFullname('User 4');
        $user4->setPassword('password', $encoderFactory);
        $user4->setGroup($group4);
        $perm4 = new GroupPermission();
        $perm4
            ->setGroup($group4)
            ->setAttributes(array('PUM_OBJ_EDIT'))
            ->setProject($project)
            ->setBeam($beam)
            ->setObject($this->object)
            ->setInstance(1)
        ;
        $group4->addAdvancedPermission($perm4);
        $this->tokenHasEditPermOnInstance = new UsernamePasswordToken($user4, null, 'secured_area', array('ROLE_USER'));

        $group5 = new Group('HasMasterPermissionOnObject');
        $user5 = new User();
        $user5->setUsername('user5@kitae.fr');
        $user5->setFullname('User 5');
        $user5->setPassword('password', $encoderFactory);
        $user5->setGroup($group5);
        $perm5 = new GroupPermission();
        $perm5
            ->setGroup($group5)
            ->setAttributes(array('PUM_OBJ_MASTER'))
            ->setProject($project)
            ->setBeam($beam)
        ;
        $group5->addAdvancedPermission($perm5);
        $this->tokenHasMasterPermOnObject = new UsernamePasswordToken($user5, null, 'secured_area', array('ROLE_USER'));

        /*$em->persist($perm1);
        $em->persist($perm2);
        $em->persist($perm3);
        $em->persist($perm4);
        $em->persist($perm5);

        $em->persist($userGroup);
        $em->persist($adminGroup);
        $em->persist($group1);
        $em->persist($group2);
        $em->persist($group3);
        $em->persist($group4);
        $em->persist($group5);

        $em->persist($freshUser);
        $em->persist($adminUser);
        $em->persist($user1);
        $em->persist($user2);
        $em->persist($user3);
        $em->persist($user4);
        $em->persist($user5);

        $em->flush();*/

        $this->voter = new ObjectVoter();
        // Need to find a solution to remap ids to doctrine query before using this
        //$this->voter = new ObjectVoter($em->getRepository('Pum\\Bundle\\AppBundle\\Entity\\UserPermission'));
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

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasViewPermOnBeam, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasViewPermOnObject, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
        );

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($this->tokenHasEditPermOnInstance, array('project' => 'FooProject'), ['PUM_OBJ_VIEW'])
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

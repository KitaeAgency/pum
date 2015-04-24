<?php

namespace Pum\Core\Tests\Security\Authorization;

use Pum\Bundle\AppBundle\Entity\Permission;

class PermissionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group permission
     */
    public function testPermissionClass()
    {
        $permission = new Permission();
        $permission->addAttribute(Permission::PERMISSION_VIEW);

        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_VIEW));
        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_EDIT));

        $permission->addAttribute(Permission::PERMISSION_ALL);

        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_VIEW));
        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_CREATE));
        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_EDIT));
        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_DELETE));

        $permission->removeAttritebute(Permission::PERMISSION_DELETE);

        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_DELETE));

        $permission->removeAttritebute(Permission::PERMISSION_EDIT);

        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_EDIT));
        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_VIEW));

        $permission->setAttributes(array());

        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_VIEW));
        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_CREATE));
        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_EDIT));
        $this->assertFalse($permission->hasAttribute(Permission::PERMISSION_DELETE));

        $permission->setAttributes(array(Permission::PERMISSION_EDIT));

        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_VIEW));
        $this->assertTrue($permission->hasAttribute(Permission::PERMISSION_EDIT));

        $this->assertEquals($permission->getAttributes(), array(
            Permission::PERMISSION_VIEW,
            Permission::PERMISSION_EDIT
        ));
    }
}

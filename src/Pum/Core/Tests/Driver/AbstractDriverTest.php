<?php

namespace Pum\Core\Tests\Driver;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;

abstract class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    abstract protected function getDriver();

    public function testEmpty()
    {
        $driver = $this->getDriver();

        $this->assertCount(0, $driver->getProjectNames());
        $this->assertCount(0, $driver->getBeamNames());
    }

    public function testSaveBeam()
    {
        $driver = $this->getDriver();

        $def = Beam::create('beam_blog')
            ->addObject(ObjectDefinition::create('blog')
                ->createField('title', 'text')
                ->createField('subtitle', 'text')
            )
        ;

        $driver->saveBeam($def);

        $this->assertEquals(array('beam_blog'), $driver->getBeamNames());
    }

    public function testMultiobject()
    {
        $driver = $this->getDriver();

        $def = Beam::create('beam_blog')
            ->addObject(ObjectDefinition::create('blog')
                ->createField('title', 'text')
                ->createField('subtitle', 'text')
            )
            ->addObject(ObjectDefinition::create('blog_post')
                ->createField('title', 'text')
                ->createField('content', 'text')
            )
        ;

        $driver->saveBeam($def);

        $this->assertCount(2, $driver->getBeam('beam_blog')->getObjects());
    }
}

<?php

namespace Pum\Core\Tests\Driver;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Exception\ProjectNotFoundException;

abstract class AbstractDriverTest extends \PHPUnit_Framework_TestCase
{
    protected $drivers = array();

    abstract protected function createDriver($hash);

    protected function getDriver($hash = null)
    {
        $hash = null === $hash ? md5(uniqid().microtime()) : $hash;

        if (isset($this->drivers[$hash])) {
            return $this->drivers[$hash];
        }

        return $this->drivers[$hash] = $this->createDriver($hash);
    }

    public function testEmpty()
    {
        $driver = $this->getDriver();

        $this->assertCount(0, $driver->getProjectNames());
        $this->assertCount(0, $driver->getBeamNames());
    }

    public function testBeams()
    {
        $driver = $this->getDriver();

        $beam = Beam::create('beam_blog')
            ->setIcon('paperplane')
            ->setColor('sanguine')
            ->addObject(ObjectDefinition::create('blog')
                ->createField('title', 'text')
                ->createField('subtitle', 'text')
            )
        ;

        $driver->saveBeam($beam);

        $this->assertEquals(array('beam_blog'), $driver->getBeamNames());
    }

    public function testBeamReplacement()
    {
        $driver = $this->getDriver();

        $beam = Beam::create('beam_blog')
            ->setIcon('paperplane')
            ->setColor('sanguine')
        ;

        $driver->saveBeam($beam);
        $driver->deleteBeam($beam);

        $beam = Beam::create('beam_blog')
            ->setIcon('paperplane')
            ->setColor('sanguine')
        ;

        $driver->saveBeam($beam);
    }

    public function testProjects()
    {
        $driver = $this->getDriver();

        $driver->saveProject(Project::create('foo'));
        $driver->saveProject(Project::create('bar'));

        $this->assertEquals(array('foo', 'bar'), $driver->getProjectNames());

        $this->assertEquals('foo', $driver->getProject('foo')->getName());

        $driver->deleteProject($driver->getProject('foo'));

        try {
            $driver->getProject('foo');
            $this->fail("No exception");
        } catch (ProjectNotFoundException $e) {
        }
    }

    public function testMultiobject()
    {
        $driver = $this->getDriver();

        $def = Beam::create('beam_blog')
            ->setIcon('paperplane')
            ->setColor('sanguine')
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

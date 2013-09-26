<?php

namespace Pum\Core\Tests\Schema;

use Pum\Core\Definition\Beam;
use Pum\Core\Definition\ObjectDefinition;
use Pum\Core\Definition\Project;
use Pum\Core\Exception\DefinitionNotFoundException;

abstract class AbstractSchemaTest extends \PHPUnit_Framework_TestCase
{
    protected $drivers = array();

    abstract protected function createSchema($hash);

    protected function getSchema($hash = null)
    {
        $hash = null === $hash ? md5(uniqid().microtime()) : $hash;

        if (isset($this->drivers[$hash])) {
            return $this->drivers[$hash];
        }

        return $this->drivers[$hash] = $this->createSchema($hash);
    }

    public function testEmpty()
    {
        $driver = $this->getSchema();

        $this->assertCount(0, $driver->getProjectNames());
        $this->assertCount(0, $driver->getBeamNames());
    }

    public function testBeams()
    {
        $driver = $this->getSchema();

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
        $driver = $this->getSchema();

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
        $driver = $this->getSchema();

        $driver->saveProject(Project::create('foo'));
        $driver->saveProject(Project::create('bar'));

        $this->assertEquals(array('foo', 'bar'), $driver->getProjectNames());

        $this->assertEquals('foo', $driver->getProject('foo')->getName());

        $driver->deleteProject($driver->getProject('foo'));

        try {
            $driver->getProject('foo');
            $this->fail("No exception");
        } catch (DefinitionNotFoundException $e) {
        }
    }

    public function testMultiobject()
    {
        $driver = $this->getSchema();

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

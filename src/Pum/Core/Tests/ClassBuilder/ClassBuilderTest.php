<?php

namespace Pum\Core\Tests\ClassBuilder;

use Pum\Core\ClassBuilder\ClassBuilder;

class ClassBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException RuntimeException
     */
    public function testInvalidClassName()
    {
        $builder = new ClassBuilder('fo o');
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidConstantName()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createConstant('cal c')
        ;
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidPropertyName()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createProperty('cal c')
        ;
    }

    /**
     * @expectedException RuntimeException
     */
    public function testInvalidMethodName()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createMethod('cal c')
        ;
    }

    public function testGetCode()
    {
        $builder = new ClassBuilder('foo');
        $code = $builder->getCode();
        $code = trim(preg_replace("/\s+/", " ", $code));
        $this->assertEquals('class foo { }', $code);
    }

    public function testExtends()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->setExtends('car')
        ;

        $this->assertEquals('car', $builder->getExtends());
    }

    public function testImplements()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->addImplements('car')
            ->addImplements('essence')
        ;

        $this->assertEquals(true, $builder->hasImplements('car'));
        $this->assertEquals(true, $builder->hasImplements('essence'));
        $this->assertEquals(false, $builder->hasImplements('noexists'));
    }

    public function testConstants()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createConstant('def')
            ->createConstant('toto', '"tata"')
            ->createConstant('hello', '"world"')
        ;

        $sample = $builder->getSample();

        $this->assertEquals("", $sample::def);
        $this->assertEquals("tata", $sample::toto);
        $this->assertEquals("world", $sample::hello);
    }

    public function testProperties()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createProperty('def')
            ->createProperty('toto', '"tata"')
            ->createProperty('hello', '"world"')
        ;

        $this->assertEquals(true, $builder->hasProperty('def'));
        $this->assertEquals(true, $builder->hasProperty('toto'));
        $this->assertEquals(true, $builder->hasProperty('hello'));
        $this->assertEquals(false, $builder->hasProperty('noexists'));
    }

    public function testMethods()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createMethod('calc', '', 'return 2 + 3;')
            ->createMethod('foo', '', 'return "foo";')
        ;

        $sample = $builder->getSample();

        $this->assertEquals(5, $sample->calc());
        $this->assertEquals("foo", $sample->foo());
    }
}

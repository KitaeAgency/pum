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

    public function testPrependOrCreateMethod()
    {
        // with a previous body
        $builder = new ClassBuilder('foo');
        $builder->createProperty('value');
        $builder->addGetMethod('value');
        $builder->createMethod('__construct', '$x = 5', '$this->value = $x;');

        $builder->prependOrCreateMethod('__construct', '$x = 5', '$x++;');
        $sample = $builder->getSample();

        $this->assertEquals(6, $sample->getValue());

        // without previous body
        $builder = new ClassBuilder('foo');
        $builder->createProperty('value');
        $builder->addGetMethod('value');
        $builder->prependOrCreateMethod('__construct', '$x = 5', '$this->value = $x;');
        $sample = $builder->getSample();

        $this->assertEquals(5, $sample->getValue());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testPrependOrCreateMethod_DifferentSignatures()
    {
        // with a previous body
        $builder = new ClassBuilder('foo');
        $builder->createProperty('value');
        $builder->addGetMethod('value');

        $builder->createMethod('__construct', '$x = 5', '$this->value = $x;');
        $builder->prependOrCreateMethod('__construct', '$x = 1', '$x++;');
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

    /**
     * @expectedException RuntimeException
     */
    public function testGetInvalidCode()
    {
        $builder = new ClassBuilder('foo');
        $builder->createMethod('getFoo', '', '$var = ;return "test";');
        $code = $builder->getCode();
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

        $builder->removeImplements('car');

        $this->assertEquals(false, $builder->hasImplements('car'));
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

        $constant = $builder->getConstant('hello');

        $this->assertEquals('hello', $constant->getName());

        $builder->removeConstant('tata');

        $this->assertEquals(false, $builder->hasConstant('tata'));
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

        $property = $builder->getProperty('hello');

        $this->assertEquals('hello', $property->getName());

        $builder->removeProperty('toto');

        $this->assertEquals(false, $builder->hasProperty('toto'));
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

        $method = $builder->getMethod('calc');

        $this->assertEquals('calc', $method->getName());

        $builder->removeMethod('calc');

        $this->assertEquals(false, $builder->hasMethod('calc'));
    }

    public function testGetSetMethods()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createProperty('hello', '"world"')
            ->addGetMethod('hello')
            ->addSetMethod('hello')
        ;

        $sample = $builder->getSample();

        $this->assertEquals("world", $sample->getHello());

        $sample->setHello('coucou');

        $this->assertEquals("coucou", $sample->getHello());
    }

    public function testAddCodeToMethods()
    {
        $builder = new ClassBuilder('foo');
        $builder
            ->createProperty('numberA', '3')
            ->createProperty('numberB', '2')
            ->createMethod('calc', '', 'return $this->numberA + $this->numberB;')
            ->getMethod('calc')->prependCode('return 10;')
        ;

        $sample = $builder->getSample();

        $this->assertEquals(10, $sample->calc());

        $builder->getMethod('calc')->prependCode('return 20;');

        $sample = $builder->getSample();

        $this->assertEquals(20, $sample->calc());
    }
}

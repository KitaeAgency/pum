<?php

namespace Pum\Core\Tests\ClassBuilder;

class ClassBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSample()
    {
        $class = new ClassBuilder('toto');
        $class->getCode(); // 'class toto {}'


        $class->addMethod('getName', '', 'return "foo";');

        eval($class->getCode());
        $toto = new toto();
        $this->assertEquals("foo", $toto->getName());

        $class = new ClassBuilder('toto2');
        $class->addMethod('getTotal', '$a, $b', 'return $a + $b;');
        eval($class->getCode());

        $toto = new toto2();
        $this->assertEquals(5, $toto->getTotal(2, 3));
    }
}

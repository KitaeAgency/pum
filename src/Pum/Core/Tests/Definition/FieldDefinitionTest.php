<?php

namespace Pum\Core\Tests\Definition;

use Pum\Core\Definition\FieldDefinition;

class FieldDefinitionTest extends \PHPUnit_Framework_TestCase
{
    public function provideCamelCase()
    {
        return array(
            array('foo', 'foo'),
            array('foo bar', 'fooBar'),
            array('FOO BAR', 'fooBar'),
            array('foo_bar', 'fooBar'),
            array('foo-bar-baz', 'fooBarBaz'),
            array('foo--    bar', 'fooBar')
        );
    }

    /**
     * @dataProvider provideCamelCase
     */
    public function testCamelCase($name, $expectedCamelCase)
    {
        $field = new FieldDefinition();
        $field->setName($name);

        $this->assertEquals($expectedCamelCase, $field->getCamelCaseName());
    }
}

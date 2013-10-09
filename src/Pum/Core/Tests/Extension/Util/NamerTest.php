<?php

namespace Pum\Util\Tests;

use Pum\Core\Definition\FieldDefinition;
use Pum\Core\Extension\Util\Namer;

class NamerTest extends \PHPUnit_Framework_TestCase
{
    public function provideCamelCase()
    {
        return array(
            array('foo', 'foo'),
            array('épée de feu', 'epeeDeFeu'),
            array('foo bar', 'fooBar'),
            array('FOO BAR', 'fooBar'),
            array('foo_bar', 'fooBar'),
            array('foo-bar-baz', 'fooBarBaz'),
            array('foo--    bar', 'fooBar'),
            array('foo >#|(] bar', 'fooBar')
        );
    }

    /**
     * @dataProvider provideCamelCase
     */
    public function testCamelCase($name, $expectedCamelCase)
    {
        $this->assertEquals($expectedCamelCase, Namer::toCamelCase($name));
    }

    public function provideLowercase()
    {
        return array(
            array('is_archive', 'is_archive'),
            array('foo', 'foo'),
            array('épée de feu', 'epee_de_feu'),
            array('FOO', 'foo'),
            array('foo bar', 'foo_bar'),
            array('FOO BAR', 'foo_bar'),
            array('foo_bar', 'foo_bar'),
            array('foo-bar-baz', 'foo_bar_baz'),
            array('foo--    bar', 'foo_bar')
        );
    }

    /**
     * @dataProvider provideLowercase
     */
    public function testLowercase($name, $expectedCamelCase)
    {
        $this->assertEquals($expectedCamelCase, Namer::toLowercase($name));
    }
}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Args\Format;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Format\CommandName;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandNameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getValidNames
     */
    public function testCreate($string)
    {
        $commandName = new CommandName($string);

        $this->assertSame($string, $commandName->toString());
    }

    /**
     * @dataProvider getValidNames
     */
    public function testCreateWithAliases($string)
    {
        $commandName = new CommandName('cmd', array('alias', $string));

        $this->assertSame('cmd', $commandName->toString());
        $this->assertSame(array('alias', $string), $commandName->getAliases());
    }

    public function testToString()
    {
        $commandName = new CommandName('cmd');

        $this->assertSame('cmd', (string) $commandName);
    }

    /**
     * @dataProvider getInvalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfInvalidString($string)
    {
        new CommandName($string);
    }

    /**
     * @dataProvider getInvalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfInvalidAlias($string)
    {
        new CommandName('cmd', array($string));
    }

    public function getValidNames()
    {
        return array(
            array('command'),
            array('COMMAND'),
            array('command-name'),
            array('c'),
            array('command1'),
        );
    }

    public function getInvalidNames()
    {
        return array(
            array('command_name'),
            array('command&'),
            array(''),
            array(null),
            array(1234),
            array(true),
        );
    }

    public function testMatch()
    {
        $commandName = new CommandName('cmd', array('alias1', 'alias2'));

        $this->assertTrue($commandName->match('cmd'));
        $this->assertTrue($commandName->match('alias1'));
        $this->assertTrue($commandName->match('alias2'));
        $this->assertFalse($commandName->match('foo'));
    }
}

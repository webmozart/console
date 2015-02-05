<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Input;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Input\CommandName;

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

    public function testToString()
    {
        $commandName = new CommandName('cmd');

        $this->assertSame('cmd', (string) $commandName);
    }

    /**
     * @dataProvider getInvalidNames
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfInvalid($string)
    {
        new CommandName($string);
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
}

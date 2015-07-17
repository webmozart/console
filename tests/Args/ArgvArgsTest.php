<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Args;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Args\ArgvArgs;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgvArgsTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $_SERVER['argv'] = array('console', 'server', 'add', '--port', '80', 'localhost');

        $args = new ArgvArgs();

        $this->assertSame('console', $args->getScriptName());
        $this->assertSame(array('server', 'add', '--port', '80', 'localhost'), $args->getTokens());
    }

    public function testCreateWithCustomTokens()
    {
        $_SERVER['argv'] = array('console', 'server', 'add', 'localhost');

        $args = new ArgvArgs(array('other', 'server', 'add', '--port', '80', 'localhost'));

        $this->assertSame('other', $args->getScriptName());
        $this->assertSame(array('server', 'add', '--port', '80', 'localhost'), $args->getTokens());
    }

    public function testCreateNoArgs()
    {
        $args = new ArgvArgs(array());

        $this->assertNull($args->getScriptName());
        $this->assertSame(array(), $args->getTokens());
    }

    public function testHasToken()
    {
        $args = new ArgvArgs(array('console', 'server', 'add', '--port', '80', 'localhost'));

        $this->assertTrue($args->hasToken('server'));
        $this->assertTrue($args->hasToken('--port'));
        $this->assertTrue($args->hasToken('80'));
        $this->assertFalse($args->hasToken('console'));
        $this->assertFalse($args->hasToken('foobar'));
    }

    public function testToString()
    {
        $args = new ArgvArgs(array('console', 'server', 'add', '--port', '80', 'localhost'));

        $this->assertSame('console server add --port 80 localhost', $args->toString());
        $this->assertSame('console server add --port 80 localhost', $args->toString(true));
        $this->assertSame('server add --port 80 localhost', $args->toString(false));
    }
}

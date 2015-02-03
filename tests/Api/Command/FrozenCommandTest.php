<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Command;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Command\FrozenCommand;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FrozenCommandTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $command = new FrozenCommand('ls');

        $this->assertSame('ls', $command->getName());
        $this->assertTrue($command->isFrozen());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfNameNull()
    {
        new FrozenCommand(null);
    }
}

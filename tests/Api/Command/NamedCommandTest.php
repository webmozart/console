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
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\CommandConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class NamedCommandTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testCreateFailsIfNoName()
    {
        new NamedCommand(new CommandConfig());
    }
}

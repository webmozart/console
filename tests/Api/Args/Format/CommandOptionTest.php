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
use Webmozart\Console\Api\Args\Format\CommandOption;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandOptionTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $option = new CommandOption('delete');

        $this->assertSame('delete', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertNull($option->getDescription());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    public function testCreateWithShortName()
    {
        $option = new CommandOption('delete', 'd');

        $this->assertSame('delete', $option->getLongName());
        $this->assertSame('d', $option->getShortName());
        $this->assertNull($option->getDescription());
        $this->assertFalse($option->isLongNamePreferred());
        $this->assertTrue($option->isShortNamePreferred());
    }

    public function testCreateWithDescription()
    {
        $option = new CommandOption('delete', null, 0, 'Description');

        $this->assertSame('delete', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertSame('Description', $option->getDescription());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    public function testCreatePreferShortName()
    {
        $option = new CommandOption('delete', 'd', CommandOption::PREFER_SHORT_NAME);

        $this->assertSame('delete', $option->getLongName());
        $this->assertSame('d', $option->getShortName());
        $this->assertNull($option->getDescription());
        $this->assertFalse($option->isLongNamePreferred());
        $this->assertTrue($option->isShortNamePreferred());
    }
}

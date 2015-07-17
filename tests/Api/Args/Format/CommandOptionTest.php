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
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandOptionTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $option = new CommandOption('delete');

        $this->assertSame('delete', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertSame(array(), $option->getLongAliases());
        $this->assertSame(array(), $option->getShortAliases());
        $this->assertNull($option->getDescription());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    public function testCreateWithShortName()
    {
        $option = new CommandOption('delete', 'd');

        $this->assertSame('delete', $option->getLongName());
        $this->assertSame('d', $option->getShortName());
        $this->assertSame(array(), $option->getLongAliases());
        $this->assertSame(array(), $option->getShortAliases());
        $this->assertNull($option->getDescription());
        $this->assertFalse($option->isLongNamePreferred());
        $this->assertTrue($option->isShortNamePreferred());
    }

    public function testCreateWithDescription()
    {
        $option = new CommandOption('delete', null, array(), 0, 'Description');

        $this->assertSame('delete', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertSame(array(), $option->getLongAliases());
        $this->assertSame(array(), $option->getShortAliases());
        $this->assertSame('Description', $option->getDescription());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    public function testCreatePreferShortName()
    {
        $option = new CommandOption('delete', 'd', array(), CommandOption::PREFER_SHORT_NAME);

        $this->assertSame('delete', $option->getLongName());
        $this->assertSame('d', $option->getShortName());
        $this->assertSame(array(), $option->getLongAliases());
        $this->assertSame(array(), $option->getShortAliases());
        $this->assertNull($option->getDescription());
        $this->assertFalse($option->isLongNamePreferred());
        $this->assertTrue($option->isShortNamePreferred());
    }

    public function testCreateWithAliases()
    {
        $option = new CommandOption('delete', null, array('alias', 'a', 'A'));

        $this->assertSame('delete', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertSame(array('alias'), $option->getLongAliases());
        $this->assertSame(array('a', 'A'), $option->getShortAliases());
        $this->assertNull($option->getDescription());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    public function testCreateWithAliasesDashes()
    {
        $option = new CommandOption('delete', null, array('--alias', '-a', '-A'));

        $this->assertSame('delete', $option->getLongName());
        $this->assertNull($option->getShortName());
        $this->assertSame(array('alias'), $option->getLongAliases());
        $this->assertSame(array('a', 'A'), $option->getShortAliases());
        $this->assertNull($option->getDescription());
        $this->assertTrue($option->isLongNamePreferred());
        $this->assertFalse($option->isShortNamePreferred());
    }

    /**
     * @dataProvider getValidAliases
     */
    public function testValidAliases($alias, array $longAliases, array $shortAliases)
    {
        $option = new CommandOption('delete', null, array($alias));

        $this->assertSame($longAliases, $option->getLongAliases());
        $this->assertSame($shortAliases, $option->getShortAliases());
    }

    public function getValidAliases()
    {
        return array(
            array('a', array(), array('a')),
            array('-a', array(), array('a')),
            array('A', array(), array('A')),
            array('-A', array(), array('A')),
            array('alias', array('alias'), array()),
            array('--alias', array('alias'), array()),
            array('alias-name', array('alias-name'), array()),
            array('--alias-name', array('alias-name'), array()),
        );
    }

    /**
     * @dataProvider getInvalidAliases
     * @expectedException \InvalidArgumentException
     */
    public function testFailIfAliasInvalid($alias)
    {
        new CommandOption('delete', null, array($alias));
    }

    public function getInvalidAliases()
    {
        return array(
            array(null),
            array(1234),
            array(''),
            array('1'),
            array('-1'),
            array('&'),
            array('_alias'),
            array('alias&'),
        );
    }
}

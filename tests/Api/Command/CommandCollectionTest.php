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
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Config\OptionCommandConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandCollection
     */
    private $collection;

    protected function setUp()
    {
        $this->collection = new CommandCollection();
    }

    public function testCreateWithCommands()
    {
        $collection = new CommandCollection(array(
            $ls = new Command(new CommandConfig('ls')),
            $cd = new Command(new CommandConfig('cd')),
        ));

        $this->assertSame(array('ls' => $ls, 'cd' => $cd), $collection->toArray());
    }

    public function testAdd()
    {
        $this->collection->add($ls = new Command(new CommandConfig('ls')));
        $this->collection->add($cd = new Command(new CommandConfig('cd')));

        // return sorted result
        $this->assertSame(array('ls' => $ls, 'cd' => $cd), $this->collection->toArray());
    }

    public function testMerge()
    {
        $this->collection->add($ls = new Command(new CommandConfig('ls')));
        $this->collection->merge(array(
            $cd = new Command(new CommandConfig('cd')),
            $cat = new Command(new CommandConfig('cat')),
        ));

        $this->assertSame(array('ls' => $ls, 'cd' => $cd, 'cat' => $cat), $this->collection->toArray());
    }

    public function testReplace()
    {
        $this->collection->add($ls = new Command(new CommandConfig('ls')));
        $this->collection->replace(array(
            $cd = new Command(new CommandConfig('cd')),
            $cat = new Command(new CommandConfig('cat')),
        ));

        $this->assertSame(array('cd' => $cd, 'cat' => $cat), $this->collection->toArray());
    }

    public function testGet()
    {
        $this->collection->add($ls = new Command(new CommandConfig('ls')));
        $this->collection->add($cd = new Command(new CommandConfig('cd')));

        $this->assertSame($ls, $this->collection->get('ls'));
        $this->assertSame($cd, $this->collection->get('cd'));
    }

    public function testGetByAlias()
    {
        $ls = new Command(CommandConfig::create('ls')->addAlias('ls-alias'));

        $this->collection->add($ls);

        $this->assertSame($ls, $this->collection->get('ls'));
        $this->assertSame($ls, $this->collection->get('ls-alias'));
    }

    public function testGetByShortName()
    {
        $ls = new Command(new OptionCommandConfig('ls', 'l'));

        $this->collection->add($ls);

        $this->assertSame($ls, $this->collection->get('ls'));
        $this->assertSame($ls, $this->collection->get('l'));
    }

    /**
     * @expectedException \Webmozart\Console\Api\Command\NoSuchCommandException
     * @expectedExceptionMessage foobar
     */
    public function testGetFailsIfNotFound()
    {
        $this->collection->get('foobar');
    }

    public function testContains()
    {
        $this->assertFalse($this->collection->contains('ls'));

        $this->collection->add($ls = new Command(new CommandConfig('ls')));

        $this->assertTrue($this->collection->contains('ls'));
    }

    public function testContainsAliases()
    {
        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('ls-alias'));

        $this->collection->add($ls = new Command(CommandConfig::create('ls')->addAlias('ls-alias')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('ls-alias'));
    }

    public function testContainsShortNames()
    {
        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('l'));

        $this->collection->add($ls = new Command(new OptionCommandConfig('ls', 'l')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('l'));
    }

    public function testRemove()
    {
        $this->collection->add(new Command(new CommandConfig('ls')));
        $this->assertTrue($this->collection->contains('ls'));
        $this->collection->remove('ls');
        $this->assertFalse($this->collection->contains('ls'));
    }

    public function testRemoveIgnoresNonExistingNames()
    {
        $this->collection->remove('foobar');
    }

    public function testRemoveAliases()
    {
        $this->collection->add(new Command(CommandConfig::create('ls')->addAlias('ls-alias')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('ls-alias'));

        $this->collection->remove('ls');

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('ls-alias'));
    }

    public function testRemoveByAlias()
    {
        $this->collection->add(new Command(CommandConfig::create('ls')->addAlias('ls-alias')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('ls-alias'));

        $this->collection->remove('ls-alias');

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('ls-alias'));
    }

    public function testRemoveIgnoresOverwrittenAliases()
    {
        $this->collection->add(new Command(CommandConfig::create('ls')->addAlias('ls-alias')));
        $this->collection->add(new Command(CommandConfig::create('ls2')->addAlias('ls-alias')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('ls2'));
        $this->assertTrue($this->collection->contains('ls-alias'));

        $this->collection->remove('ls');

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('ls2'));
        $this->assertTrue($this->collection->contains('ls-alias'));
    }

    public function testRemoveShortNames()
    {
        $this->collection->add(new Command(new OptionCommandConfig('ls', 'l')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('l'));

        $this->collection->remove('ls');

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('l'));
    }

    public function testRemoveByShortName()
    {
        $this->collection->add(new Command(new OptionCommandConfig('ls', 'l')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('l'));

        $this->collection->remove('l');

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('l'));
    }

    public function testClear()
    {
        $this->collection->add(new Command(new OptionCommandConfig('ls', 'l')));
        $this->collection->add(new Command(CommandConfig::create('cd')->addAlias('cd-alias')));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('l'));
        $this->assertTrue($this->collection->contains('cd'));
        $this->assertTrue($this->collection->contains('cd-alias'));

        $this->collection->clear();

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('l'));
        $this->assertFalse($this->collection->contains('cd'));
        $this->assertFalse($this->collection->contains('cd-alias'));
    }

    public function testGetNames()
    {
        $ls = new Command(CommandConfig::create('ls')->addAlias('ls-alias'));
        $cd = new Command(CommandConfig::create('cd')->addAlias('cd-alias'));

        $this->collection->add($ls);
        $this->collection->add($cd);

        $this->assertSame(array('cd', 'ls'), $this->collection->getNames());
    }

    public function testGetNamesWithAliases()
    {
        $ls = new Command(CommandConfig::create('ls')->addAlias('ls-alias'));
        $cd = new Command(CommandConfig::create('cd')->addAlias('cd-alias'));

        $this->collection->add($ls);
        $this->collection->add($cd);

        $this->assertSame(array('cd', 'cd-alias', 'ls', 'ls-alias'), $this->collection->getNames(true));
    }

    public function testGetAliases()
    {
        $ls = new Command(CommandConfig::create('ls')->addAlias('ls-alias'));
        $cd = new Command(CommandConfig::create('cd')->addAlias('cd-alias'));

        $this->collection->add($ls);
        $this->collection->add($cd);

        $this->assertSame(array('cd-alias' => 'cd', 'ls-alias' => 'ls'), $this->collection->getAliases());
    }

    public function testCount()
    {
        $this->assertCount(0, $this->collection);
        $this->collection->add(new Command(new CommandConfig('ls')));
        $this->assertCount(1, $this->collection);
        $this->collection->add(new Command(new CommandConfig('cd')));
        $this->assertCount(2, $this->collection);
        $this->collection->remove('ls');
        $this->assertCount(1, $this->collection);
        $this->collection->clear();
        $this->assertCount(0, $this->collection);
    }

    public function testArrayAccess()
    {
        $this->assertFalse(isset($this->collection['ls']));

        $this->collection[] = $ls = new Command(new CommandConfig('ls'));

        $this->assertTrue(isset($this->collection['ls']));
        $this->assertSame($ls, $this->collection['ls']);

        unset($this->collection['ls']);
        $this->assertFalse(isset($this->collection['ls']));

        unset($this->collection['foobar']);
        $this->assertFalse(isset($this->collection['foobar']));
    }

    /**
     * @expectedException \LogicException
     */
    public function testArrayAccessFailsIfSetWithKey()
    {
        $this->collection['foobar'] = new Command(new CommandConfig('ls'));
    }

    public function testIterator()
    {
        $this->collection->add($ls = new Command(new CommandConfig('ls')));
        $this->collection->add($cd = new Command(new CommandConfig('cd')));

        $result = iterator_to_array($this->collection);

        $this->assertSame(array('ls' => $ls, 'cd' => $cd), $result);
    }
}

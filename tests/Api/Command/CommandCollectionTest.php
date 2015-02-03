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
use Webmozart\Console\Api\Command\FrozenCommand;

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
            $ls = new FrozenCommand('ls'),
            $cd = new FrozenCommand('cd'),
        ));

        // return sorted result
        $this->assertSame(array('cd' => $cd, 'ls' => $ls), $collection->toArray());
    }

    public function testAdd()
    {
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->collection->add($cd = new FrozenCommand('cd'));

        // return sorted result
        $this->assertSame(array('cd' => $cd, 'ls' => $ls), $this->collection->toArray());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddFailsIfNotFrozen()
    {
        // the name of an unfrozen command can be changed, which breaks the
        // collection
        $this->collection->add(new Command('ls'));
    }

    public function testMerge()
    {
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->collection->merge(array(
            $cd = new FrozenCommand('cd'),
            $cat = new FrozenCommand('cat'),
        ));

        // return sorted result
        $this->assertSame(array('cat' => $cat, 'cd' => $cd, 'ls' => $ls), $this->collection->toArray());
    }

    public function testReplace()
    {
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->collection->replace(array(
            $cd = new FrozenCommand('cd'),
            $cat = new FrozenCommand('cat'),
        ));

        // return sorted result
        $this->assertSame(array('cat' => $cat, 'cd' => $cd), $this->collection->toArray());
    }

    public function testGet()
    {
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->collection->add($cd = new FrozenCommand('cd'));

        $this->assertSame($ls, $this->collection->get('ls'));
        $this->assertSame($cd, $this->collection->get('cd'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foobar
     */
    public function testGetFailsIfNotFound()
    {
        $this->collection->get('foobar');
    }

    public function testContains()
    {
        $this->assertFalse($this->collection->contains('ls'));
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->assertTrue($this->collection->contains('ls'));
    }

    public function testRemove()
    {
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->assertTrue($this->collection->contains('ls'));
        $this->collection->remove('ls');
        $this->assertFalse($this->collection->contains('ls'));
    }

    public function testRemoveIgnoresNonExistingNames()
    {
        $this->collection->remove('foobar');
    }

    public function testClear()
    {
        $this->collection->add(new FrozenCommand('ls'));
        $this->collection->add(new FrozenCommand('cd'));

        $this->assertTrue($this->collection->contains('ls'));
        $this->assertTrue($this->collection->contains('cd'));

        $this->collection->clear();

        $this->assertFalse($this->collection->contains('ls'));
        $this->assertFalse($this->collection->contains('cd'));
    }

    public function testCount()
    {
        $this->assertCount(0, $this->collection);
        $this->collection->add(new FrozenCommand('ls'));
        $this->assertCount(1, $this->collection);
        $this->collection->add(new FrozenCommand('cd'));
        $this->assertCount(2, $this->collection);
        $this->collection->remove('ls');
        $this->assertCount(1, $this->collection);
        $this->collection->clear();
        $this->assertCount(0, $this->collection);
    }

    public function testArrayAccess()
    {
        $this->assertFalse(isset($this->collection['ls']));

        $this->collection[] = $ls = new FrozenCommand('ls');

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
        $this->collection['foobar'] = new FrozenCommand('ls');
    }

    public function testIterator()
    {
        $this->collection->add($ls = new FrozenCommand('ls'));
        $this->collection->add($cd = new FrozenCommand('cd'));

        $result = iterator_to_array($this->collection);

        $this->assertSame(array('cd' => $cd, 'ls' => $ls), $result);
    }
}

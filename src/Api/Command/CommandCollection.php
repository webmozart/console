<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Command;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use LogicException;
use OutOfBoundsException;

/**
 * A collection of commands.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandCollection implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var Command[]
     */
    private $commands = array();

    /**
     * Creates a new command collection.
     *
     * @param Command[] $commands The commands to initially fill the collection
     *                            with.
     */
    public function __construct(array $commands = array())
    {
        $this->merge($commands);
    }

    /**
     * Adds a command to the collection.
     *
     * The command must be frozen, otherwise an exception is thrown.
     *
     * If a command exists with the same name in the collection, that command
     * is overwritten.
     *
     * @param Command $command The frozen command to add.
     *
     * @throws LogicException If the command is not frozen.
     *
     * @see merge(), replace()
     */
    public function add(Command $command)
    {
        if (!$command->isFrozen()) {
            throw new LogicException(sprintf(
                'The command "%s" must be frozen before adding it to the collection.',
                $command->getName()
            ));
        }

        $this->commands[$command->getName()] = $command;

        ksort($this->commands);
    }

    /**
     * Adds multiple commands to the collection.
     *
     * The commands must be frozen, otherwise an exception is thrown.
     *
     * Existing commands are preserved. Commands with the same names as the
     * passed commands are overwritten.
     *
     * @param Command[] $commands The frozen commands to add.
     *
     * @throws LogicException If a command is not frozen.
     *
     * @see add(), replace()
     */
    public function merge(array $commands)
    {
        foreach ($commands as $command) {
            $this->add($command);
        }
    }

    /**
     * Sets the commands in the collection.
     *
     * The commands must be frozen, otherwise an exception is thrown.
     *
     * Existing commands are replaced.
     *
     * @param Command[] $commands The frozen commands to set.
     *
     * @throws LogicException If a command is not frozen.
     *
     * @see add(), merge()
     */
    public function replace(array $commands)
    {
        $this->clear();
        $this->merge($commands);
    }

    /**
     * Returns a command by its name.
     *
     * @param string $name The name of the command.
     *
     * @return Command The command.
     *
     * @throws OutOfBoundsException If no command with that name is in the
     *                              collection.
     */
    public function get($name)
    {
        if (!isset($this->commands[$name])) {
            throw new OutOfBoundsException(sprintf(
                'The command "%s" does not exist.',
                $name
            ));
        }

        return $this->commands[$name];
    }

    /**
     * Removes the command with the given name from the collection.
     *
     * If no such command can be found, this method does nothing.
     *
     * @param string $name The name of the command.
     */
    public function remove($name)
    {
        unset($this->commands[$name]);
    }

    /**
     * Returns whether the collection contains a command with the given name.
     *
     * @param string $name The name of the command.
     *
     * @return bool Returns `true` if the collection contains a command with
     *              that name and `false` otherwise.
     */
    public function contains($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * Removes all commands frmo the collection.
     */
    public function clear()
    {
        $this->commands = array();
    }

    /**
     * Returns the contents of the collection as array.
     *
     * The commands in the collection are returned indexed by their names. The
     * result is sorted alphabetically by the command names.
     *
     * @return Command[] The commands sorted by their names in ascending order.
     */
    public function toArray()
    {
        return $this->commands;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($name)
    {
        return $this->contains($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $command)
    {
        if ($offset) {
            throw new LogicException('Passing of offsets is not supported');
        }

        $this->add($command);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }


    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->commands);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->commands);
    }
}

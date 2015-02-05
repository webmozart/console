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
     * @var string[]
     */
    private $aliases = array();

    /**
     * Creates a new command collection.
     *
     * @param Command[] $commands The commands to initially add to the collection.
     */
    public function __construct(array $commands = array())
    {
        $this->merge($commands);
    }

    /**
     * Adds a command to the collection.
     *
     * If a command exists with the same name in the collection, that command
     * is overwritten.
     *
     * @param Command $command The command to add.
     *
     * @see merge(), replace()
     */
    public function add(Command $command)
    {
        $this->commands[$command->getName()] = $command;

        foreach ($command->getAliases() as $alias) {
            $this->aliases[$alias] = $command->getName();
        }

        ksort($this->commands);
        ksort($this->aliases);
    }

    /**
     * Adds multiple commands to the collection.
     *
     * Existing commands are preserved. Commands with the same names as the
     * passed commands are overwritten.
     *
     * @param Command[] $commands The commands to add.
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
     * Existing commands are replaced.
     *
     * @param Command[] $commands The commands to set.
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
     * @throws OutOfBoundsException If no command with that name exists in the
     *                              collection.
     */
    public function get($name)
    {
        if (isset($this->commands[$name])) {
            return $this->commands[$name];
        }

        if (isset($this->aliases[$name])) {
            return $this->commands[$this->aliases[$name]];
        }

        throw new OutOfBoundsException(sprintf(
            'The command "%s" does not exist.',
            $name
        ));
    }

    /**
     * Removes the command with the given name from the collection.
     *
     * If no such command can be found, the method does nothing.
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
     * Removes all commands from the collection.
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
     * @return Command[] The commands indexed and sorted by their names in
     *                   ascending order.
     */
    public function toArray()
    {
        return $this->commands;
    }

    /**
     * Returns the names of all commands in the collection.
     *
     * The names are sorted alphabetically in ascending order. If you set
     * `$includeAliases` to `true`, the alias names are included in the result.
     *
     * @param bool $includeAliases Whether to include alias names in the result.
     *
     * @return string[] The sorted command names.
     */
    public function getNames($includeAliases = false)
    {
        $names = array_keys($this->commands);

        if ($includeAliases) {
            $names = array_merge($names, array_keys($this->aliases));
            sort($names);
        }

        return $names;
    }

    /**
     * Returns the aliases of all commands in the collection.
     *
     * The aliases are sorted alphabetically in ascending order.
     *
     * @return string[] The sorted command aliases.
     */
    public function getAliases()
    {
        return $this->aliases;
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

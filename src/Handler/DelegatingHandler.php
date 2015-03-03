<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Handler;

use InvalidArgumentException;
use LogicException;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Assert\Assert;

/**
 * Delegates command handling to one of a list of registered handlers.
 *
 * You can register handlers or factory callbacks that create those handlers
 * with the {@link register()} method:
 *
 * ```php
 * $handler = new DelegatingHandler();
 * $handler->register('json', new JsonHandler());
 * $handler->register('xml', function () {
 *     $handler = new XmlHandler();
 *     // ...
 *
 *     return $handler;
 * });
 * ```
 *
 * The executed handler can be selected with {@link selectHandler()}. You need
 * to pass the name of the handler or a callback that receives the command,
 * the console arguments and the I/O as parameters:
 *
 * ```php
 * $handler->selectHandler(function (Command $command, Args $args, IO $io) {
 *     return $args->getOption('format');
 * });
 * ```
 *
 * Run {@link handle()} to execute the selected handler:
 *
 * ```php
 * $handler->handle($command, $args, $io);
 * ```
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DelegatingHandler
{
    /**
     * @var object[]|callable[]
     */
    private $handlers = array();

    /**
     * @var string|callable
     */
    private $selectedHandler;

    /**
     * {@inheritdoc}
     */
    public function handle(Args $args, IO $io, Command $command)
    {
        $handlerName = $this->selectedHandler;

        if (!$handlerName) {
            throw new LogicException('No handler was selected.');
        }

        if (is_callable($handlerName)) {
            $handlerName = call_user_func($handlerName, $args, $io, $command);
        }

        if (!isset($this->handlers[$handlerName])) {
            throw new LogicException(sprintf(
                'The handler "%s" does not exist.',
                $handlerName
            ));
        }

        $handler = $this->handlers[$handlerName];

        if (is_callable($handler)) {
            $handler = call_user_func($handler, $args, $io, $command);
        }

        return $handler->handle($args, $io, $command);
    }

    /**
     * Registers a command handler for the given name.
     *
     * @param string          $name    The handler name.
     * @param object|callable $handler The handler or a factory callback that
     *                                 creates the handler.
     */
    public function register($name, $handler)
    {
        Assert::string($name, 'The handler name must be a string. Got: %s');
        Assert::notEmpty($name, 'The handler name must not be empty.');

        if (!is_object($handler)) {
            Assert::isCallable($handler, 'The handler must be a callable or an object. Got: %s');
        }

        $this->handlers[$name] = $handler;

        if (!$this->selectedHandler) {
            $this->selectedHandler = $name;
        }
    }

    /**
     * Unregisters the command handler for the given name.
     *
     * @param string $name The handler name.
     */
    public function unregister($name)
    {
        unset($this->handlers[$name]);

        if ($name === $this->selectedHandler) {
            reset($this->handlers);
            $this->selectedHandler = key($this->handlers);
        }
    }

    /**
     * Returns all registered handler names.
     *
     * @return string[] The handler names.
     */
    public function getRegisteredNames()
    {
        return array_keys($this->handlers);
    }

    /**
     * Selects the executed handler.
     *
     * @param string|callback $handler The name of the handler or a callback
     *                                 that returns the name. The callback
     *                                 receives the executed {@link Command},
     *                                 the {@link Args} and the {@link IO} as
     *                                 arguments.
     */
    public function selectHandler($handler)
    {
        if (!is_callable($handler)) {
            Assert::string($handler, 'The selected handler must be a callable or a string. Got: %s');

            if (!isset($this->handlers[$handler])) {
                throw new InvalidArgumentException(sprintf(
                    'The handler "%s" does not exist.',
                    $handler
                ));
            }
        }

        $this->selectedHandler = $handler;
    }
}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Adapter;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\RawArgs;

/**
 * Adapts an {@link Args} instance to Symfony's {@link InputInterface} API.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsInput implements InputInterface
{
    /**
     * @var RawArgs
     */
    private $rawArgs;

    /**
     * @var Args
     */
    private $args;

    /**
     * Creates the adapter.
     *
     * @param RawArgs $rawArgs The unparsed console arguments.
     * @param Args    $args    The parsed console arguments.
     */
    public function __construct(RawArgs $rawArgs, Args $args = null)
    {
        $this->rawArgs = $rawArgs;
        $this->args = $args;
    }

    /**
     * @return RawArgs
     */
    public function getRawArgs()
    {
        return $this->rawArgs;
    }

    /**
     * @return Args
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstArgument()
    {
        $tokens = $this->rawArgs->getTokens();

        return count($tokens) > 0 ? reset($tokens) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values)
    {
        $tokens = $this->rawArgs->getTokens();

        foreach ((array) $values as $value) {
            foreach ($tokens as $token) {
                if ($token === $value || 0 === strpos($token, $value.'=')) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false)
    {
        $tokens = $this->rawArgs->getTokens();

        foreach ((array) $values as $value) {
            for (reset($tokens); null !== key($tokens); next($tokens)) {
                $token = current($tokens);

                // Long/short option with value in the next argument
                if ($token === $value) {
                    return next($tokens) ?: null;
                }

                // Long option with =
                if (0 === strpos($token, $value.'=')) {
                    return substr($token, strlen($value) + 1);
                }

                // Short option
                if (strlen($token) > 2 && '-' === $token[0] && '-' !== $token[1] && 0 === strpos($token, $value)) {
                    return substr($token, 2);
                }
            }
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function bind(InputDefinition $definition)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->args ? $this->args->getArguments() : array();
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($name)
    {
        return $this->args ? $this->args->getArgument($name) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setArgument($name, $value)
    {
        if ($this->args) {
            $this->args->setArgument($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasArgument($name)
    {
        return $this->args ? $this->args->isArgumentDefined($name) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->args ? $this->args->getOptions() : array();
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        return $this->args ? $this->args->getOption($name) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        if ($this->args) {
            $this->args->setOption($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return $this->args ? $this->args->isOptionDefined($name) : false;
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function setInteractive($interactive)
    {
    }
}

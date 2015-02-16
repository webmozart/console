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
use Webmozart\Console\Api\Input\Input;

/**
 * An input that wraps the console arguments and the standard input.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CompositeInput implements InputInterface
{
    /**
     * @var RawArgs
     */
    private $rawArgs;

    /**
     * @var Input
     */
    private $input;

    /**
     * @var Args
     */
    private $args;

    public function __construct(RawArgs $rawArgs, Input $input, Args $args = null)
    {
        $this->rawArgs = $rawArgs;
        $this->input = $input;
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
     * @return Input
     */
    public function getInput()
    {
        return $this->input;
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
        foreach ((array) $values as $value) {
            if (!$this->rawArgs->hasToken($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false)
    {

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

    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($name)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setArgument($name, $value)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function hasArgument($name)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setInteractive($interactive)
    {

    }
}

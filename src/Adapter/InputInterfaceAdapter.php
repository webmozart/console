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
use Webmozart\Console\Api\Input\Input;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputInterfaceAdapter implements Input
{
    /**
     * @var InputInterface
     */
    private $adaptedInput;

    public function __construct(InputInterface $adaptedInput)
    {
        $this->adaptedInput = $adaptedInput;
    }

    /**
     * @return InputInterface
     */
    public function getAdaptedInput()
    {
        return $this->adaptedInput;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstArgument()
    {
        return $this->adaptedInput->getFirstArgument();
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameterOption($values)
    {
        return $this->adaptedInput->hasParameterOption($values);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterOption($values, $default = false)
    {
        return $this->adaptedInput->getParameterOption($values, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(InputDefinition $definition)
    {
        return $this->adaptedInput->bind($definition);
    }

    /**
     * {@inheritdoc}
     */
    public function validate()
    {
        return $this->adaptedInput->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments()
    {
        return $this->adaptedInput->getArguments();
    }

    /**
     * {@inheritdoc}
     */
    public function getArgument($name)
    {
        return $this->adaptedInput->getArgument($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setArgument($name, $value)
    {
        return $this->adaptedInput->setArgument($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasArgument($name)
    {
        return $this->adaptedInput->hasArgument($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->adaptedInput->getOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name)
    {
        return $this->adaptedInput->getOption($name);
    }

    /**
     * {@inheritdoc}
     */
    public function setOption($name, $value)
    {
        return $this->adaptedInput->setOption($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return $this->adaptedInput->hasOption($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isInteractive()
    {
        return $this->adaptedInput->isInteractive();
    }

    /**
     * {@inheritdoc}
     */
    public function setInteractive($interactive)
    {
        return $this->adaptedInput->setInteractive($interactive);
    }

    /**
     * {@inheritdoc}
     */
    public function toString()
    {
        return $this->adaptedInput->__toString();
    }
}

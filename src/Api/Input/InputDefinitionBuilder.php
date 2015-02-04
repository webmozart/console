<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Input;

use LogicException;
use OutOfBoundsException;
use Webmozart\Console\Assert\Assert;

/**
 * A builder for input definitions.
 *
 * Use the methods in this class to dynamically build {@link InputDefinition}
 * instances. When you are done configuring the builder, call
 * {@link buildDefinition()} to build an immutable {@link InputDefinition}
 * instance:
 *
 * ```php
 * $builder = new InputDefinitionBuilder();
 * $builder->addArgument(new InputArgument('input'));
 * $builder->addArgument(new InputArgument('output'));
 * $builder->addOption(new InputOption('format', 'f', InputOption::VALUE_REQUIRED));
 *
 * $definition = $builder->buildDefinition();
 * ```
 *
 * You can optionally pass a base input definition. The arguments of the base
 * definition are prepended to the arguments of the built definition. The
 * options of the base definition are appended to the built options:
 *
 * ```php
 * $baseDefinition = new InputDefinition(array(
 *     new InputOption('verbose', 'v'),
 * ));
 *
 * $builder = new InputDefinitionBuilder($baseDefinition);
 * $builder->addArgument(new InputArgument('input'));
 * $builder->addArgument(new InputArgument('output'));
 * $builder->addOption(new InputOption('format', 'f', InputOption::VALUE_REQUIRED));
 *
 * $definition = $builder->buildDefinition();
 * ```
 *
 * @since  1.0
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @see    InputDefinition
 */
class InputDefinitionBuilder
{
    /**
     * @var InputDefinition
     */
    private $baseDefinition;

    /**
     * @var InputArgument[]
     */
    private $arguments = array();

    /**
     * @var InputOption[]
     */
    private $options = array();

    /**
     * @var InputOption[]
     */
    private $optionsByShortName = array();

    /**
     * @var bool
     */
    private $hasMultiValuedArg = false;

    /**
     * @var bool
     */
    private $hasOptionalArg = false;

    /**
     * Creates a new builder.
     *
     * You can optionally pass a base input definition. The built input
     * definition inherits all the arguments and options of the base
     * definition.
     *
     * @param InputDefinition $baseDefinition The base input definition.
     */
    public function __construct(InputDefinition $baseDefinition = null)
    {
        $this->baseDefinition = $baseDefinition;
    }

    /**
     * Returns the base input definition.
     *
     * @return InputDefinition The base input definition.
     */
    public function getBaseDefinition()
    {
        return $this->baseDefinition;
    }

    /**
     * Sets the arguments of the built definition.
     *
     * Any existing arguments are removed when this method is called.
     *
     * @param InputArgument[] $arguments The arguments of the built input
     *                                   definition.
     *
     * @throws LogicException If an incorrect argument is given.
     *
     * @see appendArgument()
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = array();
        $this->hasOptionalArg = false;
        $this->hasMultiValuedArg = false;

        $this->appendArguments($arguments);
    }

    /**
     * Adds arguments at the end of the argument list.
     *
     * The existing arguments stored in the builder are preserved.
     *
     * @param InputArgument[] $arguments The arguments to append.
     *
     * @throws LogicException If an incorrect argument is given.
     *
     * @see appendArgument()
     */
    public function appendArguments(array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->appendArgument($argument);
        }
    }

    /**
     * Adds an argument at the end of the argument list.
     *
     * The existing arguments stored in the builder are preserved.
     *
     * You cannot add arguments after adding a multi-valued argument. If you do
     * so, this method throws an exception.
     *
     * Adding required arguments after optional arguments is not supported.
     * Also in this case an exception is thrown.
     *
     * @param InputArgument $argument The argument to append.
     *
     * @throws LogicException If an incorrect argument is given.
     */
    public function appendArgument(InputArgument $argument)
    {
        $name = $argument->getName();

        if ($this->hasArgument($name)) {
            throw new LogicException(sprintf('An argument with the name "%s" exists already.', $name));
        }

        if ($this->hasMultiValuedArgument()) {
            throw new LogicException('Cannot add an argument after a multi-valued argument.');
        }

        if ($argument->isRequired() && $this->hasOptionalArgument()) {
            throw new LogicException('Cannot add a required argument after an optional one.');
        }

        if ($argument->isMultiValued()) {
            $this->hasMultiValuedArg = true;
        }

        if ($argument->isOptional()) {
            $this->hasOptionalArg = true;
        }

        $this->arguments[$name] = $argument;
    }

    /**
     * Adds arguments at the beginning of the argument list.
     *
     * The existing arguments stored in the builder are preserved.
     *
     * @param InputArgument[] $arguments The arguments to prepend.
     *
     * @throws LogicException If an incorrect argument is given.
     *
     * @see prependArgument()
     */
    public function prependArguments(array $arguments)
    {
        for ($i = count($arguments) - 1; $i >= 0; --$i) {
            $this->prependArgument($arguments[$i]);
        }
    }

    /**
     * Adds an argument at the beginning of the argument list.
     *
     * The existing arguments stored in the builder are preserved.
     *
     * You cannot insert multi-valued arguments before other arguments. If you
     * do so, this method throws an exception.
     *
     * Inserting optional arguments before required arguments is not supported.
     * Also in this case an exception is thrown.
     *
     * @param InputArgument $argument The argument to prepend.
     *
     * @throws LogicException If an incorrect argument is given.
     */
    public function prependArgument(InputArgument $argument)
    {
        $name = $argument->getName();

        if ($this->hasArgument($name)) {
            throw new LogicException(sprintf('An argument with the name "%s" exists already.', $name));
        }

        if ($argument->isMultiValued() && $this->hasArguments(false)) {
            throw new LogicException('Cannot insert multi-valued arguments before other arguments.');
        }

        if ($argument->isOptional() && $this->hasRequiredArgument(false)) {
            throw new LogicException('Cannot insert optional arguments before required ones.');
        }

        if ($argument->isMultiValued()) {
            $this->hasMultiValuedArg = true;
        }

        if ($argument->isOptional()) {
            $this->hasOptionalArg = true;
        }

        $this->arguments = array_merge(array($name => $argument), $this->arguments);
    }

    /**
     * Returns whether the builder contains a specific argument.
     *
     * You can either pass the name of the argument or the 0-based position of
     * the argument.
     *
     * @param string|int $name        The argument name or its 0-based position
     *                                in the argument list.
     * @param bool       $includeBase Whether to include arguments in the base
     *                                input definition in the search.
     *
     * @return bool Returns `true` if the argument with the given name or
     *              position could be found and `false` otherwise.
     */
    public function hasArgument($name, $includeBase = true)
    {
        if (!is_int($name)) {
            Assert::string($name, 'The argument name must be a string or an integer. Got: %s');
            Assert::notEmpty($name, 'The argument name must not be empty.');
        }

        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        $arguments = is_int($name)
            ? array_values($this->getArguments($includeBase))
            : $this->getArguments($includeBase);

        return isset($arguments[$name]);
    }

    /**
     * Returns whether the builder contains a multi-valued argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the builder contains a multi-valued
     *              argument and `false` otherwise.
     */
    public function hasMultiValuedArgument($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if ($this->hasMultiValuedArg) {
            return true;
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->hasMultiValuedArgument();
        }

        return false;
    }

    /**
     * Returns whether the builder contains an optional argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the builder contains an optional argument
     *              and `false` otherwise.
     */
    public function hasOptionalArgument($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if ($this->hasOptionalArg) {
            return true;
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->hasOptionalArgument();
        }

        return false;
    }

    /**
     * Returns whether the builder contains a required argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the builder contains a required argument
     *              and `false` otherwise.
     */
    public function hasRequiredArgument($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if (!$this->hasOptionalArg && count($this->arguments) > 0) {
            return true;
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->hasRequiredArgument();
        }

        return false;
    }

    /**
     * Returns whether the builder contains any argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the builder contains any argument and
     *              `false` otherwise.
     */
    public function hasArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if (count($this->arguments) > 0) {
            return true;
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->hasArguments();
        }

        return false;
    }

    /**
     * Returns an argument by its name or position.
     *
     * You can either pass the name of the argument or the 0-based position of
     * the argument.
     *
     * @param string|int $name        The argument name or its 0-based position
     *                                in the argument list.
     * @param bool       $includeBase Whether to include arguments in the base
     *                                input definition in the search.
     *
     * @return InputArgument The argument.
     *
     * @throws OutOfBoundsException If the argument with the given name or
     *                              position does not exist.
     */
    public function getArgument($name, $includeBase = true)
    {
        if (!is_int($name)) {
            Assert::string($name, 'The argument name must be a string or integer. Got: %s');
            Assert::notEmpty($name, 'The argument name must not be empty.');
        }

        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        $arguments = is_int($name)
            ? array_values($this->getArguments($includeBase))
            : $this->getArguments($includeBase);

        if (!isset($arguments[$name])) {
            throw new OutOfBoundsException(sprintf(
                'The argument %s does not exist.',
                is_int($name) ? sprintf('at position %s', $name) : '"'.$name.'"'
            ));
        }

        return $arguments[$name];
    }

    /**
     * Returns all arguments added to the builder.
     *
     * @param bool $includeBase Whether to include arguments of the base input
     *                          definition in the result.
     *
     * @return InputArgument[] The arguments.
     */
    public function getArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        $arguments = $this->arguments;

        if ($includeBase && $this->baseDefinition) {
            // prepend base arguments
            $arguments = array_replace($this->baseDefinition->getArguments(), $arguments);
        }

        return $arguments;
    }

    /**
     * Sets the options of the built definition.
     *
     * Any existing options are removed when this method is called.
     *
     * @param InputOption[] $options The options of the built input definition.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see appendOption()
     */
    public function setOptions(array $options)
    {
        $this->options = array();
        $this->optionsByShortName = array();

        $this->appendOptions($options);
    }

    /**
     * Adds options at the end of the options list.
     *
     * The existing options stored in the builder are preserved.
     *
     * @param InputOption[] $options The options to append.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see appendOption()
     */
    public function appendOptions(array $options)
    {
        foreach ($options as $option) {
            $this->appendOption($option);
        }
    }

    /**
     * Adds an option at the end of the options list.
     *
     * The existing options stored in the builder are preserved.
     *
     * @param InputOption $option The option to append.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see appendOptions()
     */
    public function appendOption(InputOption $option)
    {
        $longName = $option->getLongName();
        $shortName = $option->getShortName();

        if (isset($this->options[$longName])) {
            throw new LogicException(sprintf('An option named "--%s" exists already.', $longName));
        }

        if (isset($this->optionsByShortName[$shortName])) {
            throw new LogicException(sprintf('An option named "-%s" exists already.', $shortName));
        }

        $this->options[$longName] = $option;

        if ($option->getShortName()) {
            $this->optionsByShortName[$option->getShortName()] = $option;
        }
    }

    /**
     * Adds options at the beginning of the options list.
     *
     * The options are inserted in the same order as they are passed to this
     * method.
     *
     * The existing options stored in the builder are preserved.
     *
     * @param InputOption[] $options The options to prepend.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see prependOption()
     */
    public function prependOptions(array $options)
    {
        for ($i = count($options) - 1; $i >= 0; --$i) {
            $this->prependOption($options[$i]);
        }
    }

    /**
     * Adds an option at the beginning of the options list.
     *
     * The existing options stored in the builder are preserved.
     *
     * @param InputOption $option The option to prepend.
     *
     * @throws LogicException If an incorrect option is given.
     *
     * @see prependOptions()
     */
    public function prependOption(InputOption $option)
    {
        $longName = $option->getLongName();
        $shortName = $option->getShortName();

        if (isset($this->options[$longName])) {
            throw new LogicException(sprintf('An option named "--%s" exists already.', $longName));
        }

        if (isset($this->optionsByShortName[$shortName])) {
            throw new LogicException(sprintf('An option named "-%s" exists already.', $shortName));
        }

        $this->options = array_merge(array($longName => $option), $this->options);

        if ($option->getShortName()) {
            $this->optionsByShortName[$option->getShortName()] = $option;
        }
    }

    /**
     * Returns whether the builder contains a specific option.
     *
     * You can either pass the long or the short name of the option.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include options in the base input
     *                            definition in the search.
     *
     * @return bool Returns `true` if the option with the given name could be
     *              found and `false` otherwise.
     */
    public function hasOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string or an integer. Got: %s');
        Assert::notEmpty($name, 'The option name must not be empty.');
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if (isset($this->options[$name]) || isset($this->optionsByShortName[$name])) {
            return true;
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->hasOption($name);
        }

        return false;
    }

    /**
     * Returns whether the builder contains any option.
     *
     * @param bool $includeBase Whether to include options in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the builder contains any option and
     *              `false` otherwise.
     */
    public function hasOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if (count($this->options) > 0) {
            return true;
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->hasOptions();
        }

        return false;
    }

    /**
     * Returns an option by its long or short name.
     *
     * @param string $name        The long or short option name.
     * @param bool   $includeBase Whether to include options in the base input
     *                            definition in the search.
     *
     * @return InputOption The option.
     *
     * @throws OutOfBoundsException If the option with the given name does not
     *                              not exist.
     */
    public function getOption($name, $includeBase = true)
    {
        Assert::string($name, 'The option name must be a string. Got: %s');
        Assert::notEmpty($name, 'The option name must not be empty.');
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        if (isset($this->optionsByShortName[$name])) {
            return $this->optionsByShortName[$name];
        }

        if ($includeBase && $this->baseDefinition) {
            return $this->baseDefinition->getOption($name);
        }

        throw new OutOfBoundsException(sprintf(
            'The option "%s" does not exist.',
            $name
        ));
    }

    /**
     * Returns all options added to the builder.
     *
     * @param bool $includeBase Whether to include options of the base input
     *                          definition in the result.
     *
     * @return InputOption[] The options.
     */
    public function getOptions($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        $options = $this->options;

        if ($includeBase && $this->baseDefinition) {
            // append base options
            $options = array_replace($options, $this->baseDefinition->getOptions());
        }

        return $options;
    }

    /**
     * Builds an input definition with the arguments and options added to the
     * builder.
     *
     * @return InputDefinition The built input definition.
     */
    public function buildDefinition()
    {
        return new InputDefinition(array_merge(
            $this->arguments,
            $this->options
        ), $this->baseDefinition);
    }
}

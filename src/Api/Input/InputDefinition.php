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

use InvalidArgumentException;
use OutOfBoundsException;
use Webmozart\Console\Assert\Assert;

/**
 * Defines the options and arguments that may be passed for a command.
 *
 * An input definition is a container for {@link InputOption} and
 * {@link InputArgument} instances. Input definitions are used to validate
 * whether the console input matches the rules defined by the executed command.
 *
 * You can pass the options and arguments to the constructor of the class:
 *
 * ```php
 * $definition = new InputDefinition(array(
 *     new InputArgument('input'),
 *     new InputArgument('output'),
 *     new InputOption('all', 'a'),
 * ));
 * ```
 *
 * The input definition is immutable after its construction. If you want to
 * create an input definition stepwisely, you should use the
 * {@link InputDefinitionBuilder} class.
 *
 * If multiple input definitions share a common set of options and arguments,
 * you can extract these options and arguments into a base input definition and
 * let the other input definitions inherit from this base definition:
 *
 * ```php
 * $baseDefinition = new InputDefinition(array(
 *     new InputOption('verbose', 'v'),
 * ));
 *
 * $definition = new InputDefinition(array(
 *     new InputArgument('input'),
 *     new InputArgument('output'),
 *     new InputOption('all', 'a'),
 * ), $baseDefinition);
 * ```
 *
 * @since  1.0
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputDefinition
{
    /**
     * @var InputDefinition
     */
    private $baseDefinition;

    /**
     * @var InputArgument[]
     */
    private $arguments;

    /**
     * @var InputOption[]
     */
    private $options;

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
     * Creates a new input definition.
     *
     * You can optionally pass a base input definition. The input definition
     * inherits all the arguments and options of the base definition.
     *
     * @param array|InputDefinitionBuilder $elements       The arguments and
     *                                                     options or a builder
     *                                                     instance.
     * @param InputDefinition              $baseDefinition The base definition.
     */
    public function __construct($elements = array(), InputDefinition $baseDefinition = null)
    {
        if ($elements instanceof InputDefinitionBuilder && null === $baseDefinition) {
            $baseDefinition = $elements->getBaseDefinition();
        }

        if (!$elements instanceof InputDefinitionBuilder) {
            $elements = $this->createBuilderForElements($elements, $baseDefinition);
        }

        $this->baseDefinition = $baseDefinition;
        $this->arguments = $elements->getArguments(false);
        $this->options = $elements->getOptions(false);
        $this->hasMultiValuedArg = $elements->hasMultiValuedArgument(false);
        $this->hasOptionalArg = $elements->hasOptionalArgument(false);

        foreach ($this->options as $option) {
            if ($option->getShortName()) {
                $this->optionsByShortName[$option->getShortName()] = $option;
            }
        }
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
            Assert::string($name, 'The argument name must be a string or an integer. Got: %s');
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
     * Returns all arguments of the input definition.
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
     * Returns whether the input definition contains a specific argument.
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
     * Returns whether the input definition contains a multi-valued argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the input definition contains a
     *              multi-valued argument and `false` otherwise.
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
     * Returns whether the input definition contains an optional argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the input definition contains an optional
     *              argument and `false` otherwise.
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
     * Returns whether the input definition contains a required argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the input definition contains a required
     *              argument and `false` otherwise.
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
     * Returns whether the input definition contains any argument.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the input definition contains any argument
     *              and `false` otherwise.
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
     * Returns the number of arguments.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the result.
     *
     * @return int The number of arguments.
     */
    public function getNumberOfArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        if ($this->hasMultiValuedArg) {
            return PHP_INT_MAX;
        }

        return count($this->getArguments($includeBase));
    }

    /**
     * Returns the number of required arguments.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the result.
     *
     * @return int The number of required arguments.
     */
    public function getNumberOfRequiredArguments($includeBase = true)
    {
        Assert::boolean($includeBase, 'The parameter $includeBase should be a boolean. Got: %s');

        $arguments = $this->getArguments($includeBase);
        $count = 0;

        foreach ($arguments as $argument) {
            if (!$argument->isRequired()) {
                continue;
            }

            if ($argument->isMultiValued()) {
                return PHP_INT_MAX;
            }

            ++$count;
        }

        return $count;
    }

    /**
     * Returns the default values of the arguments stored in the input
     * definition.
     *
     * @param bool $includeBase Whether to include arguments in the base input
     *                          definition in the result.
     *
     * @return array The default values indexed by the names of the arguments.
     */
    public function getDefaultArgumentValues($includeBase = true)
    {
        $arguments = $this->getArguments($includeBase);
        $defaultValues = array();

        foreach ($arguments as $argument) {
            $defaultValues[$argument->getName()] = $argument->getDefaultValue();
        }

        return $defaultValues;
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
        Assert::string($name, 'The option name must be a string or an integer. Got: %s');
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
            'The "%s%s" option does not exist.',
            strlen($name) > 1 ? '--' : '-',
            $name
        ));
    }

    /**
     * Returns all options of the input definition.
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
     * Returns whether the input definition contains a specific option.
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
     * Returns whether the input definition contains any option.
     *
     * @param bool $includeBase Whether to include options in the base input
     *                          definition in the search.
     *
     * @return bool Returns `true` if the input definition contains any option
     *              and `false` otherwise.
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
     * Returns the default values of the options stored in the input
     * definition.
     *
     * @param bool $includeBase Whether to include options in the base input
     *                          definition in the result.
     *
     * @return array The default values indexed by the long names of the options.
     */
    public function getDefaultOptionValues($includeBase = true)
    {
        $options = $this->getOptions($includeBase);
        $defaultValues = array();

        foreach ($options as $option) {
            $defaultValues[$option->getLongName()] = $option->getDefaultValue();
        }

        return $defaultValues;
    }

    /**
     * Creates an input definition builder for a set of arguments and options.
     *
     * @param array           $elements       The arguments and options to add
     *                                        to the builder.
     * @param InputDefinition $baseDefinition The base input definition.
     *
     * @return InputDefinitionBuilder The created builder object.
     */
    private function createBuilderForElements(array $elements, InputDefinition $baseDefinition = null)
    {
        $builder = new InputDefinitionBuilder($baseDefinition);

        foreach ($elements as $element) {
            if ($element instanceof InputOption) {
                $builder->addOption($element);
            } elseif ($element instanceof InputArgument) {
                $builder->addArgument($element);
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Expected instances of InputOption or InputArgument. Got: %s',
                    is_object($element) ? get_class($element) : gettype($element)
                ));
            }
        }

        return $builder;
    }
}

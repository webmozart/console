<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Descriptor;

use InvalidArgumentException;
use Symfony\Component\Console\Descriptor\ApplicationDescription;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Application;
use Webmozart\Console\Command\Command;
use Webmozart\Console\Command\CompositeCommand;
use Webmozart\Console\Input\InputOption;

/**
 * Describes an object as text on the console output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TextDescriptor implements DescriptorInterface
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var OutputFormatterInterface
     */
    private $filterFormatter;

    /**
     * @var int|null
     */
    private $terminalWidth;

    /**
     * Describes an object as text on the console output.
     *
     * @param OutputInterface          $output  The output.
     * @param Command|Application $object  The object to describe.
     * @param array                    $options Additional options.
     */
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        $this->output = $output;
        $this->filterFormatter = clone $output->getFormatter();
        $this->filterFormatter->setDecorated(false);

        if ($object instanceof Command) {
            list ($this->terminalWidth) = $object->getApplication()->getTerminalDimensions();
            $this->describeCommand($object, $options);

            return;
        }

        if ($object instanceof Application) {
            list ($this->terminalWidth) = $object->getTerminalDimensions();
            $this->describeApplication($object, $options);

            return;
        }

        throw new InvalidArgumentException(sprintf(
            'Object of type "%s" is not describable.',
            is_object($object) ? get_class($object) : gettype($object)
        ));
    }

    /**
     * Describes an application.
     *
     * @param Application $application The application to describe.
     * @param array            $options     Additional options.
     */
    protected function describeApplication(Application $application, array $options = array())
    {
        $description = new ApplicationDescription($application);
        $help = $application->getHelp();
        $commands = $description->getCommands();
        $definition = $application->getDefinition();
        $inputArgs = $definition ? $definition->getArguments() : array();
        $inputOpts = $definition ? $definition->getOptions() : array();

        $options['nameWidth'] = max(
            $this->getMaxCommandWidth($commands),
            $this->getMaxOptionWidth($inputOpts),
            $this->getMaxArgumentWidth($inputArgs)
        );

        if ($help) {
            $this->printApplicationHelp($help, $options);
            $this->write("\n");
        }

        $this->printApplicationUsage($application, $options);

        $this->write("\n");

        if ($inputArgs) {
            $this->printInputArguments($inputArgs, $options);
        }

        if ($inputArgs && ($inputOpts || $commands)) {
            $this->write("\n");
        }

        if ($inputOpts) {
            $this->printInputOptions($inputOpts, $options);
        }

        if ($inputOpts && $commands) {
            $this->write("\n");
        }

        $this->printCommands($commands, $options);

        $this->write("\n");
    }

    /**
     * Describes a command.
     *
     * @param Command $command The command to describe.
     * @param array   $options Additional options.
     */
    protected function describeCommand(Command $command, array $options = array())
    {
        $command->mergeApplicationDefinition(false);

        $aliases = $command->getAliases();
        $help = $command->getProcessedHelp();
        $definition = $command->getNativeDefinition();
        $inputArgs = $this->filterArguments($definition ? $definition->getArguments() : array());
        $inputOpts = $definition ? $definition->getOptions() : array();

        $options['nameWidth'] = max(
            $this->getMaxOptionWidth($inputOpts),
            $this->getMaxArgumentWidth($inputArgs)
        );

        $this->printCommandUsage($command, $options);

        $this->write("\n");

        if ($aliases) {
            $this->printAliases($aliases, $options);
        }

        if ($aliases && ($inputArgs || $inputOpts || $help)) {
            $this->write("\n");
        }

        if ($inputArgs) {
            $this->printInputArguments($inputArgs, $options);
        }

        if ($inputArgs && ($inputOpts || $help)) {
            $this->write("\n");
        }

        if ($inputOpts) {
            $this->printInputOptions($inputOpts, $options);
        }

        if ($inputOpts && $help) {
            $this->write("\n");
        }

        if ($help) {
            $this->printCommandHelp($help, $options);
        }

        $this->write("\n");
    }

    /**
     * Prints the usage of an application.
     *
     * @param Application $application The application to describe.
     * @param array            $options     Additional options.
     */
    protected function printApplicationUsage(Application $application, array $options = array())
    {
        $executableName = $application->getExecutableName();
        $synopsis = $application->getDefinition()->getSynopsis();

        $this->write('<h>USAGE</h>');
        $this->write("\n");

        $this->printWrappedText($synopsis, '<tt>'.$executableName.'</tt>');
        $this->write("\n");
    }

    /**
     * Prints the usage of a command.
     *
     * @param Command $command The command to describe.
     * @param array   $options Additional options.
     */
    protected function printCommandUsage(Command $command, array $options = array())
    {
        $executableName = $command->getApplication()->getExecutableName();
        // Don't underline the spaces between the names
        $nameParts = array_merge(array($executableName), explode(' ', $command->getName()));
        $commandName = '<tt>'.implode('</tt> <tt>', $nameParts).'</tt>';
        $synopsises = $command->getSynopsises();
        $prefix = count($synopsises) > 1 ? '    ' : '';

        $this->write('<h>USAGE</h>');
        $this->write("\n");

        foreach ($synopsises as $synopsis) {
            $this->printWrappedText($synopsis, $prefix.$commandName);
            $this->write("\n");
            $prefix = 'or: ';
        }
    }

    /**
     * Prints a list of input arguments.
     *
     * @param InputArgument[] $inputArgs The input arguments to describe.
     * @param array           $options   Additional options.
     */
    protected function printInputArguments($inputArgs, array $options = array())
    {
        $this->write('<h>ARGUMENTS</h>');
        $this->write("\n");

        foreach ($inputArgs as $argument) {
            $this->printInputArgument($argument, $options);
            $this->write("\n");
        }
    }

    /**
     * Prints an input argument.
     *
     * @param InputArgument $argument The input argument to describe.
     * @param array         $options  Additional options.
     */
    protected function printInputArgument(InputArgument $argument, array $options = array())
    {
        $nameWidth = isset($options['nameWidth']) ? $options['nameWidth'] : null;
        $description = $argument->getDescription();
        $name = $argument->getName();

        if (null !== $argument->getDefault() && (!is_array($argument->getDefault()) || count($argument->getDefault()))) {
            $description .= sprintf('<h> (default: %s)</h>', $this->formatDefaultValue($argument->getDefault()));
        }

        $this->printWrappedText($description, '<em><'.$name.'></em>', $nameWidth, 2);
    }

    /**
     * Prints a list of input options.
     *
     * @param InputOption[] $inputOpts The input options to describe.
     * @param array         $options   Additional options.
     */
    protected function printInputOptions($inputOpts, array $options = array())
    {
        $this->write('<h>OPTIONS</h>');
        $this->write("\n");

        foreach ($inputOpts as $option) {
            $this->printInputOption($option, $options);
            $this->write("\n");
        }
    }

    /**
     * Prints an input option.
     *
     * @param InputOption $option  The input option to describe.
     * @param array       $options Additional options.
     */
    protected function printInputOption(InputOption $option, array $options = array())
    {
        $nameWidth = isset($options['nameWidth']) ? $options['nameWidth'] : null;
        $description = $option->getDescription();
        $name = '<em>--'.$option->getName().'</em>';

        if ($option->getShortcut()) {
            $name .= sprintf(' (-%s)', $option->getShortcut());
        }

        if ($option->acceptValue() && null !== $option->getDefault() && (!is_array($option->getDefault()) || count($option->getDefault()))) {
            $description .= sprintf(' (default: %s)', $this->formatDefaultValue($option->getDefault()));
        }

        if ($option->isArray()) {
            $description .= ' (multiple values allowed)';
        }

        $this->printWrappedText($description, $name, $nameWidth, 2);
    }

    /**
     * Prints the commands of an application.
     *
     * @param Command[] $commands The commands to describe.
     * @param array     $options  Additional options.
     */
    protected function printCommands($commands, array $options = array())
    {
        if (!isset($options['printCompositeCommands'])) {
            $options['printCompositeCommands'] = false;
        }

        $this->write('<h>AVAILABLE COMMANDS</h>');
        $this->write("\n");

        foreach ($commands as $command) {
            if ($command instanceof CompositeCommand && !$options['printCompositeCommands']) {
                continue;
            }

            $this->printCommand($command, $options);
            $this->write("\n");
        }
    }

    /**
     * Prints a command of an application.
     *
     * @param Command $command The command to describe.
     * @param array   $options Additional options.
     */
    protected function printCommand(Command $command, array $options = array())
    {
        $nameWidth = isset($options['nameWidth']) ? $options['nameWidth'] : null;
        $description = $command->getDescription();
        $name = $command->getName();

        $this->printWrappedText($description, '<em>'.$name.'</em>', $nameWidth, 2);
    }

    /**
     * Prints the aliases of a command.
     *
     * @param string[] $aliases The aliases to describe.
     * @param array    $options Additional options.
     */
    protected function printAliases($aliases, array $options = array())
    {
        $this->write('  aliases: '.implode(', ', $aliases));
        $this->write("\n");
    }

    /**
     * Prints the help of an application.
     *
     * @param string $help    The help text.
     * @param array  $options Additional options.
     */
    protected function printApplicationHelp($help, array $options = array())
    {
        $this->write("$help\n");
    }

    /**
     * Prints the help of a command.
     *
     * @param string $help    The help text.
     * @param array  $options Additional options.
     */
    protected function printCommandHelp($help, array $options = array())
    {
        $this->write('<h>Help</h>');
        $this->write("\n");
        $this->write(' '.str_replace("\n", "\n ", $help));
        $this->write("\n");
    }

    /**
     * Prints wrapped text.
     *
     * The text will be wrapped to match the terminal width (if available) with
     * a leading and a trailing space.
     *
     * You can optionally pass a label that is written before the text. The
     * text will then be wrapped to start each line one space to the right of
     * the label.
     *
     * If the label should have a minimum width, pass the `$labelWidth`
     * parameter. You can highlight the label by setting `$highlightLabel` to
     * `true`.
     *
     * @param string   $text           The text to write.
     * @param string   $label          The label.
     * @param int|null $minLabelWidth  The minimum width of the label.
     * @param int      $labelDistance  The distance between the label and the
     *                                 text in spaces.
     */
    protected function printWrappedText($text, $label = '', $minLabelWidth = null, $labelDistance = 1)
    {
        $visibleLabel = $this->filterStyleTags($label);
        $styleTagLength = strlen($label) - strlen($visibleLabel);
        $prefixSpace = '  ';

        if (!$minLabelWidth) {
            $minLabelWidth = strlen($visibleLabel);
        }

        // If we know the terminal width, wrap the text
        if ($this->terminalWidth) {
            // 1 space after the label
            $indentation = $minLabelWidth ? $minLabelWidth + $labelDistance : 0;
            $linePrefix = $prefixSpace.str_repeat(' ', $indentation);

            // 1 trailing space
            $textWidth = $this->terminalWidth - 1 - strlen($linePrefix);

            $text = str_replace("\n", "\n".$linePrefix, wordwrap($text, $textWidth));
        }

        if ($label) {
            // Add the total length of the style tags ("<h>", ...)
            $minLabelWidth += $styleTagLength;

            $text = sprintf(
                "%-${minLabelWidth}s%-{$labelDistance}s%s",
                $label,
                '',
                $text
            );
        }

        $this->write($prefixSpace.$text);
    }

    /**
     * Writes text to the output.
     *
     * @param string $text
     */
    protected function write($text)
    {
        $this->output->write($text, false, OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * Returns the maximum width of the names of a list of options.
     *
     * @param InputOption[] $options The options.
     *
     * @return int The maximum width.
     */
    protected function getMaxOptionWidth(array $options)
    {
        $width = 0;

        foreach ($options as $option) {
            // Respect leading dashes "--"
            $length = strlen($option->getName()) + 2;

            if ($option->getShortcut()) {
                // Respect space, dash and braces " (-", ")"
                $length += strlen($option->getShortcut()) + 4;
            }

            $width = max($width, $length);
        }

        return $width;
    }

    /**
     * Returns the maximum width of the names of a list of arguments.
     *
     * @param InputArgument[] $arguments The arguments.
     *
     * @return int The maximum width.
     */
    protected function getMaxArgumentWidth(array $arguments)
    {
        $width = 0;

        foreach ($arguments as $argument) {
            // Respect wrapping brackets "<", ">"
            $width = max($width, strlen($argument->getName()) + 2);
        }

        return $width;
    }

    /**
     * Returns the maximum width of the names of a list of commands.
     *
     * @param Command[] $commands The commands.
     *
     * @return int The maximum width.
     */
    protected function getMaxCommandWidth(array $commands)
    {
        $width = 0;

        foreach ($commands as $command) {
            $width = max($width, strlen($command->getName()));
        }

        return $width;
    }

    /**
     * Filters arguments that should not be described.
     *
     * Commands contain additional arguments that contain the command name.
     * This is necessary so that the input definition can be correctly bound
     * to the input. However, that argument should not be displayed on the
     * output, since it is not really an argument, but rather part of the
     * called command.
     *
     * @param InputArgument[] $arguments The arguments to filter.
     *
     * @return InputArgument[] The filtered arguments.
     */
    protected function filterArguments($arguments)
    {
        $filter = function (InputArgument $arg) {
            return !in_array($arg->getName(), array(
                Command::COMMAND_ARG,
                CompositeCommand::SUB_COMMAND_ARG
            ));
        };

        return array_filter($arguments, $filter);
    }

    protected function filterStyleTags($text)
    {
        return $this->filterFormatter->format($text);
    }

    /**
     * Formats the default value of an argument or an option.
     *
     * @param mixed $default The default value to format.
     *
     * @return string The formatted value.
     */
    private function formatDefaultValue($default)
    {
        if (PHP_VERSION_ID < 50400) {
            return str_replace('\/', '/', json_encode($default));
        }

        return json_encode($default, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

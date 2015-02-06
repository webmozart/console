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
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputDefinitionBuilder;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Api\TerminalDimensions;

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
     * @var TerminalDimensions
     */
    private $terminalDimensions;

    public function __construct(TerminalDimensions $terminalDimensions = null)
    {
        $this->terminalDimensions = $terminalDimensions ?: TerminalDimensions::forCurrentWindow();
    }

    /**
     * Describes an object as text on the console output.
     *
     * @param OutputInterface                 $output  The output.
     * @param Command|Application $object  The object to describe.
     * @param array                           $options Additional options.
     */
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        $this->output = $output;
        $this->filterFormatter = clone $output->getFormatter();
        $this->filterFormatter->setDecorated(false);

        if ($object instanceof Command) {
            $this->describeCommand($object, $options);

            return;
        }

        if ($object instanceof Application) {
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
     * @param array       $options     Additional options.
     */
    protected function describeApplication(Application $application, array $options = array())
    {
        $commands = $application->getCommands();

        $definitionBuilder = new InputDefinitionBuilder($application->getBaseInputDefinition());
        $definitionBuilder->addArgument(new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'));
        $definitionBuilder->addArgument(new InputArgument('arg', InputArgument::MULTI_VALUED, 'The arguments of the command'));
        $inputDefinition = $definitionBuilder->getDefinition();
        $inputArgs = $inputDefinition->getArguments();
        $inputOpts = $inputDefinition->getOptions();

        $options['nameWidth'] = max(
            $this->getMaxCommandWidth($commands),
            $this->getMaxOptionWidth($inputOpts),
            $this->getMaxArgumentWidth($inputArgs)
        );

        $this->printApplicationUsage($application, $inputDefinition, $options);

        $this->write("\n");

        if ($inputArgs) {
            $this->printInputArguments($inputArgs, $options);
        }

        if ($inputArgs && ($inputOpts || !$commands->isEmpty())) {
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
        $aliases = $command->getAliases();
        $help = $command->getConfig()->getHelp();
        $inputDefinition = $command->getInputDefinition();
        $baseDefinition = $inputDefinition->getBaseDefinition();
        $inputArgs = $inputDefinition->getArguments();
        $inputOpts = $inputDefinition->getOptions(false);
        $baseOpts = $baseDefinition ? $baseDefinition->getOptions() : array();

        $options['nameWidth'] = max(
            $this->getMaxOptionWidth($inputOpts),
            $this->getMaxOptionWidth($baseOpts),
            $this->getMaxArgumentWidth($inputArgs)
        );

        $this->printCommandUsage($command, $options);

        $this->write("\n");

        if ($aliases) {
            $this->printAliases($aliases, $options);
        }

        if ($aliases && ($inputArgs || $inputOpts || $baseOpts || $help)) {
            $this->write("\n");
        }

        if ($inputArgs) {
            $this->printInputArguments($inputArgs, $options);
        }

        if ($inputArgs && ($inputOpts || $baseOpts || $help)) {
            $this->write("\n");
        }

        if ($inputOpts) {
            $this->printInputOptions($inputOpts, $options);
        }

        if ($inputOpts && ($baseOpts || $help)) {
            $this->write("\n");
        }

        if ($baseOpts) {
            $this->printBaseInputOptions($baseOpts, $options);
        }

        if ($baseOpts && $help) {
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
     * @param Application     $application     The application to describe.
     * @param InputDefinition $inputDefinition The input definition of the
     *                                         application.
     * @param array           $options         Additional options.
     */
    protected function printApplicationUsage(Application $application, InputDefinition $inputDefinition, array $options = array())
    {
        $executableName = $application->getConfig()->getExecutableName();

        $this->printApplicationName($application, $options);

        $this->write('<h>USAGE</h>');
        $this->write("\n");

        $this->printSynopsis($inputDefinition, $executableName, '', true);
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
        $executableName = $command->getApplication()->getConfig()->getExecutableName();
        $prefix = $command->hasSubCommands() || $command->hasOptionCommands() ? '    ' : '';
        $subCommands = $command->getSubCommands();
        $optionCommands = $command->getOptionCommands();

        $this->write('<h>USAGE</h>');
        $this->write("\n");

        if ($defaultSubCommand = $command->getDefaultSubCommand()) {
            $this->printSynopsis($defaultSubCommand->getInputDefinition(), $executableName, $prefix, false, true);
            unset($subCommands[$defaultSubCommand->getName()]);
        } elseif ($defaultOptionCommand = $command->getDefaultOptionCommand()) {
            $this->printSynopsis($defaultOptionCommand->getInputDefinition(), $executableName, $prefix, false, true);
            unset($optionCommands[$defaultOptionCommand->getName()]);
        } else {
            $this->printSynopsis($command->getInputDefinition(), $executableName, $prefix);
        }

        $this->write("\n");

        foreach ($subCommands as $subCommand) {
            $this->printSynopsis($subCommand->getInputDefinition(), $executableName, 'or: ');
            $this->write("\n");
        }

        foreach ($optionCommands as $optionCommand) {
            $this->printSynopsis($optionCommand->getInputDefinition(), $executableName, 'or: ');
            $this->write("\n");
        }
    }

    /**
     * Prints a list of input arguments.
     *
     * @param InputArgument[] $inputArgs The input arguments to describe.
     * @param array           $options   Additional options.
     */
    protected function printInputArguments(array $inputArgs, array $options = array())
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
        $defaultValue = $argument->getDefaultValue();

        if (null !== $defaultValue && (!is_array($defaultValue) || count($defaultValue))) {
            $description .= sprintf('<h> (default: %s)</h>', $this->formatDefaultValue($defaultValue));
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
     * Prints a list of global input options.
     *
     * @param InputOption[] $inputOpts The input options to describe.
     * @param array         $options   Additional options.
     */
    protected function printBaseInputOptions($inputOpts, array $options = array())
    {
        $this->write('<h>GLOBAL OPTIONS</h>');
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
        $name = '<em>--'.$option->getLongName().'</em>';
        $defaultValue = $option->getDefaultValue();

        if ($option->getShortName()) {
            $name .= sprintf(' (-%s)', $option->getShortName());
        }

        if ($option->acceptsValue() && null !== $defaultValue && (!is_array($defaultValue) || count($defaultValue))) {
            $description .= sprintf(' (default: %s)', $this->formatDefaultValue($defaultValue));
        }

        if ($option->isMultiValued()) {
            $description .= ' (multiple values allowed)';
        }

        $this->printWrappedText($description, $name, $nameWidth, 2);
    }

    protected function printApplicationName(Application $application, array $options = array())
    {
        $config = $application->getConfig();

        if ('UNKNOWN' !== $config->getName() && 'UNKNOWN' !== $config->getVersion()) {
            $this->write(sprintf("<info>%s</info> version <comment>%s</comment>\n", $config->getName(), $config->getVersion()));
        } else {
            $this->write("<info>Console Tool</info>\n");
        }

        $this->write("\n");
    }

    /**
     * Prints the commands of an application.
     *
     * @param CommandCollection $commands The commands to describe.
     * @param array             $options  Additional options.
     */
    protected function printCommands(CommandCollection $commands, array $options = array())
    {
        $this->write('<h>AVAILABLE COMMANDS</h>');
        $this->write("\n");

        foreach ($commands as $command) {
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
        $description = $command->getConfig()->getDescription();
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

    protected function printSynopsis(InputDefinition $inputDefinition, $executableName, $prefix = '', $includeBaseOptions = false, $lastCommandOptional = false)
    {
        $nameParts = array();
        $argumentParts = array();

        $nameParts[] = '<tt>'.$executableName.'</tt>';

        foreach ($inputDefinition->getCommandNames() as $commandName) {
            $nameParts[] = '<tt>'.$commandName->toString().'</tt>';
        }

        foreach ($inputDefinition->getCommandOptions() as $commandOption) {
            // TODO display preferred name
            $nameParts[] = '-'.$commandOption->getShortName();
        }

        if ($lastCommandOptional) {
            $lastIndex = count($nameParts) - 1;
            $nameParts[$lastIndex] = '['.$nameParts[$lastIndex].']';
        }

        foreach ($inputDefinition->getOptions($includeBaseOptions) as $option) {
            // \xC2\xA0 is a non-breaking space
            if ($option->isValueRequired()) {
                $format = "--%s\xC2\xA0<%s>";
            } elseif ($option->isValueOptional()) {
                $format = "--%s\xC2\xA0[<%s>]";
            } else {
                $format = '--%s';
            }

            $argumentParts[] = sprintf('['.$format.']', $option->getLongName(), $option->getValueName());
        }

        foreach ($inputDefinition->getArguments() as $argument) {
            $name = $argument->getName();

            $argumentParts[] = sprintf(
                $argument->isRequired() ? '<%s>' : '[<%s>]',
                $name.($argument->isMultiValued() ? '1' : '')
            );

            if ($argument->isMultiValued()) {
                $argumentParts[] = sprintf('... [<%sN>]', $name);
            }
        }

        $this->printWrappedText(implode(' ', $argumentParts), $prefix.implode(' ', $nameParts));
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
        if ($this->terminalDimensions->getWidth()) {
            // 1 space after the label
            $indentation = $minLabelWidth ? $minLabelWidth + $labelDistance : 0;
            $linePrefix = $prefixSpace.str_repeat(' ', $indentation);

            // 1 trailing space
            $textWidth = $this->terminalDimensions->getWidth() - 1 - strlen($linePrefix);

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

        $this->write(rtrim($prefixSpace.$text));
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
            $length = strlen($option->getLongName()) + 2;

            if ($option->getShortName()) {
                // Respect space, dash and braces " (-", ")"
                $length += strlen($option->getShortName()) + 4;
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
     * @param CommandCollection $commands The commands.
     *
     * @return int The maximum width.
     */
    protected function getMaxCommandWidth(CommandCollection $commands)
    {
        $width = 0;

        foreach ($commands as $command) {
            $width = max($width, strlen($command->getName()));
        }

        return $width;
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

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
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Rendering\Element\EmptyLine;
use Webmozart\Console\Rendering\Element\LabeledParagraph;
use Webmozart\Console\Rendering\Element\Paragraph;
use Webmozart\Console\Rendering\Layout\BlockLayout;

/**
 * Describes an object as text on the console output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TextDescriptor implements Descriptor
{
    /**
     * @var \Webmozart\Console\Rendering\Layout\BlockLayout
     */
    private $layout;

    /**
     * Describes an object as text on the console output.
     *
     * @param Output              $output  The output.
     * @param Command|Application $object  The object to describe.
     * @param array               $options Additional options.
     *
     * @return int The exit code.
     */
    public function describe(Output $output, $object, array $options = array())
    {
        $this->layout = new BlockLayout();

        if ($object instanceof Command) {
            $this->describeCommand($object, $options);
        } elseif ($object instanceof Application) {
            $this->describeApplication($object, $options);
        } else {
            throw new InvalidArgumentException(sprintf(
                'Object of type "%s" is not describable.',
                is_object($object) ? get_class($object) : gettype($object)
            ));
        }

        $this->layout->render($output);

        return 0;
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

        $argsFormat = ArgsFormat::build($application->getGlobalArgsFormat())
            ->addArgument(new Argument('command', Argument::REQUIRED, 'The command to execute'))
            ->addArgument(new Argument('arg', Argument::MULTI_VALUED, 'The arguments of the command'))
            ->getFormat();

        $this->printApplicationName($application, $options);
        $this->printApplicationUsage($application, $argsFormat, $options);
        $this->printArguments($argsFormat->getArguments(), $options);

        if ($argsFormat->hasOptions()) {
            $this->printGlobalOptions($argsFormat->getOptions(), $options);
        }

        if (!$commands->isEmpty()) {
            $this->printCommands($commands, $options);
        }
    }

    /**
     * Describes a command.
     *
     * @param Command $command The command to describe.
     * @param array   $options Additional options.
     */
    protected function describeCommand(Command $command, array $options = array())
    {
        $help = $command->getConfig()->getHelp();
        $argsFormat = $command->getArgsFormat();
        $subCommands = $command->getSubCommands();
        $optCommands = $command->getOptionCommands();

        $this->printCommandUsage($command, $options);

        if ($argsFormat->hasArguments()) {
            $this->printArguments($argsFormat->getArguments(), $options);
        }

        if (!$subCommands->isEmpty() || !$optCommands->isEmpty()) {
            $this->printSubCommands($subCommands, $optCommands, $options);
        }

        if ($argsFormat->hasOptions(false)) {
            $this->printOptions($argsFormat->getOptions(false), $options);
        }

        if ($argsFormat->getBaseFormat() && $argsFormat->getBaseFormat()->hasOptions()) {
            $this->printGlobalOptions($argsFormat->getBaseFormat()->getOptions(), $options);
        }

        if ($help) {
            $this->printCommandHelp($help, $options);
        }
    }

    /**
     * Prints the usage of an application.
     *
     * @param Application $application The application to describe.
     * @param ArgsFormat  $argsFormat  The format of the console arguments.
     * @param array       $options     Additional options.
     */
    protected function printApplicationUsage(Application $application, ArgsFormat $argsFormat, array $options = array())
    {
        $appName = $application->getConfig()->getName();

        $this->layout->add(new Paragraph("<h>USAGE</h>"));
        $this->layout->beginBlock();

        // true: print global options
        $this->printSynopsis($argsFormat, $appName, '', true);

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints the usage of a command.
     *
     * @param Command $command The command to describe.
     * @param array   $options Additional options.
     *
     * @return string The output.
     */
    protected function printCommandUsage(Command $command, array $options = array())
    {
        $formatsToPrint = array();

        // Start with the default commands
        if ($command->hasDefaultCommands()) {
            // If the command has default commands, print them
            foreach ($command->getDefaultCommands() as $subCommand) {
                if ($subCommand instanceof NamedCommand) {
                    // true: wrap the sub-command name in "[" "]"
                    $formatsToPrint[$subCommand->getName()] = array($subCommand->getArgsFormat(), true);
                } else {
                    $formatsToPrint[] = array($subCommand->getArgsFormat(), false);
                }
            }
        } else {
            // Otherwise print the command's usage itself
            $formatsToPrint[] = array($command->getArgsFormat(), false);
        }

        // Add remaining sub-commands
        foreach ($command->getSubCommands() as $subCommand) {
            // Don't duplicate default commands
            if (!isset($formatsToPrint[$subCommand->getName()])) {
                $formatsToPrint[$subCommand->getName()] = array($subCommand->getArgsFormat(), false);
            }
        }
        foreach ($command->getOptionCommands() as $optionCommand) {
            // Don't duplicate default commands
            if (!isset($formatsToPrint[$optionCommand->getName()])) {
                $formatsToPrint[$optionCommand->getName()] = array($optionCommand->getArgsFormat(), false);
            }
        }

        $appName = $command->getApplication()->getConfig()->getName();
        $prefix = count($formatsToPrint) > 1 ? '    ' : '';

        $this->layout->add(new Paragraph('<h>USAGE</h>'));
        $this->layout->beginBlock();

        foreach ($formatsToPrint as $vars) {
            // false: don't include global options
            $this->printSynopsis($vars[0], $appName, $prefix, false, $vars[1]);
            $prefix = 'or: ';
        }

        if ($command instanceof NamedCommand && $command->hasAliases()) {
            $this->layout->add(new EmptyLine());
            $this->printAliases($command->getAliases(), $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints a list of arguments.
     *
     * @param Argument[] $arguments The arguments to describe.
     * @param array      $options   Additional options.
     *
     * @return string The output.
     */
    protected function printArguments(array $arguments, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>ARGUMENTS</h>'));
        $this->layout->beginBlock();

        foreach ($arguments as $argument) {
            $this->printArgument($argument, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints an argument.
     *
     * @param Argument $argument The argument to describe.
     * @param array    $options  Additional options.
     *
     * @return string The output.
     */
    protected function printArgument(Argument $argument, array $options = array())
    {
        $description = $argument->getDescription();
        $name = '<em><'.$argument->getName().'></em>';
        $defaultValue = $argument->getDefaultValue();

        if (null !== $defaultValue && (!is_array($defaultValue) || count($defaultValue))) {
            $description .= sprintf('<h> (default: %s)</h>', $this->formatDefaultValue($defaultValue));
        }

        $this->layout->add(new LabeledParagraph($name, $description));
    }

    /**
     * Prints a list of options.
     *
     * @param Option[] $opts    The options to describe.
     * @param array    $options Additional options.
     *
     * @return string The output.
     */
    protected function printOptions(array $opts, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>OPTIONS</h>'));
        $this->layout->beginBlock();

        foreach ($opts as $option) {
            $this->printInputOption($option, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints a list of global options.
     *
     * @param Option[] $opts    The global options to describe.
     * @param array    $options Additional options.
     *
     * @return string The output.
     */
    protected function printGlobalOptions($opts, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>GLOBAL OPTIONS</h>'));
        $this->layout->beginBlock();

        foreach ($opts as $option) {
            $this->printInputOption($option, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints an input option.
     *
     * @param Option $option  The option to describe.
     * @param array  $options Additional options.
     */
    protected function printInputOption(Option $option, array $options = array())
    {
        $description = $option->getDescription();
        $defaultValue = $option->getDefaultValue();

        if ($option->isLongNamePreferred()) {
            $preferredName = '--'.$option->getLongName();
            $alternativeName = $option->getShortName() ? '-'.$option->getShortName() : null;
        } else {
            $preferredName = '-'.$option->getShortName();
            $alternativeName = '--'.$option->getLongName();
        }

        $name = '<em>'.$preferredName.'</em>';

        if ($alternativeName) {
            $name .= sprintf(' (%s)', $alternativeName);
        }

        if ($option->acceptsValue() && null !== $defaultValue && (!is_array($defaultValue) || count($defaultValue))) {
            $description .= sprintf(' (default: %s)', $this->formatDefaultValue($defaultValue));
        }

        if ($option->isMultiValued()) {
            $description .= ' (multiple values allowed)';
        }

        $this->layout->add(new LabeledParagraph($name, $description));
    }

    protected function printSubCommands(CommandCollection $subCommands, CommandCollection $optionCommands, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>COMMANDS</h>'));
        $this->layout->beginBlock();

        foreach ($subCommands as $subCommand) {
            $this->printSubCommand($subCommand, $options);
        }

        foreach ($optionCommands as $optionCommand) {
            $this->printSubCommand($optionCommand, $options);
        }

        $this->layout->endBlock();
    }

    protected function printSubCommand(NamedCommand $command, array $options = array())
    {
        $config = $command->getConfig();
        $description = $config->getDescription();
        $help = $config->getHelp();
        $inputArgs = $command->getArgsFormat()->getArguments(false);
        $inputOpts = $command->getArgsFormat()->getOptions(false);

        if ($config instanceof OptionCommandConfig) {
            if ($config->isLongNamePreferred()) {
                $preferredName = '--'.$config->getLongName();
                $alternativeName = $config->getShortName() ? '-'.$config->getShortName() : null;
            } else {
                $preferredName = '-'.$config->getShortName();
                $alternativeName = '--'.$config->getLongName();
            }

            $name = $preferredName;

            if ($alternativeName) {
                $name .= ', '.$alternativeName;
            }
        } else {
            $name = $command->getName();
        }

        $this->layout->add(new Paragraph("<tt>$name</tt>"));
        $this->layout->beginBlock();

        if ($description) {
            $this->printSubCommandDescription($description, $options);
        }

        if ($help) {
            $this->printSubCommandHelp($help, $options);
        }

        if ($inputArgs) {
            $this->printSubCommandArguments($inputArgs, $options);
        }

        if ($inputOpts) {
            $this->printSubCommandOptions($inputOpts, $options);
        }

        $this->layout->endBlock();
    }

    protected function printSubCommandDescription($description, array $options = array())
    {
        $this->layout->add(new Paragraph($description));
        $this->layout->add(new EmptyLine());
    }

    protected function printSubCommandHelp($help, array $options = array())
    {
        $this->layout->add(new Paragraph($help));
        $this->layout->add(new EmptyLine());
    }

    protected function printSubCommandArguments(array $inputArgs, array $options = array())
    {
        foreach ($inputArgs as $argument) {
            $this->printArgument($argument, $options);
        }

        $this->layout->add(new EmptyLine());
    }

    protected function printSubCommandOptions(array $inputOpts, array $options = array())
    {
        foreach ($inputOpts as $option) {
            $this->printInputOption($option, $options);
        }

        $this->layout->add(new EmptyLine());
    }

    protected function printApplicationName(Application $application, array $options = array())
    {
        $config = $application->getConfig();

        if ($config->getDisplayName() && $config->getVersion()) {
            $this->layout->add(new Paragraph("<info>{$config->getDisplayName()}</info> version <comment>{$config->getVersion()}</comment>"));
        } else {
            $this->layout->add(new Paragraph("<info>Console Tool</info>"));
        }

        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints the commands of an application.
     *
     * @param CommandCollection $commands The commands to describe.
     * @param array             $options  Additional options.
     */
    protected function printCommands(CommandCollection $commands, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>AVAILABLE COMMANDS</h>'));
        $this->layout->beginBlock();

        foreach ($commands as $command) {
            $this->printCommand($command, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints a command of an application.
     *
     * @param NamedCommand $command The command to describe.
     * @param array        $options Additional options.
     */
    protected function printCommand(NamedCommand $command, array $options = array())
    {
        $description = $command->getConfig()->getDescription();
        $name = '<em>'.$command->getName().'</em>';

        $this->layout->add(new LabeledParagraph($name, $description));
    }

    /**
     * Prints the aliases of a command.
     *
     * @param string[] $aliases The aliases to describe.
     * @param array    $options Additional options.
     */
    protected function printAliases($aliases, array $options = array())
    {
        $this->layout->add(new Paragraph('aliases: '.implode(', ', $aliases)));
    }

    /**
     * Prints the help of an application.
     *
     * @param string $help    The help text.
     * @param array  $options Additional options.
     */
    protected function printApplicationHelp($help, array $options = array())
    {
        $this->layout->add(new Paragraph($help));
    }

    /**
     * Prints the help of a command.
     *
     * @param string $help    The help text.
     * @param array  $options Additional options.
     */
    protected function printCommandHelp($help, array $options = array())
    {
        $this->layout
            ->add(new Paragraph('<h>DESCRIPTION</h>'))
            ->beginBlock()
                ->add(new Paragraph($help))
            ->endBlock()
            ->add(new EmptyLine())
        ;
    }

    protected function printSynopsis(ArgsFormat $argsFormat, $appName, $prefix = '', $includeBaseOptions = false, $lastCommandOptional = false)
    {
        $nameParts = array();
        $argumentParts = array();

        $nameParts[] = '<tt>'.$appName.'</tt>';

        foreach ($argsFormat->getCommandNames() as $commandName) {
            $nameParts[] = '<tt>'.$commandName->toString().'</tt>';
        }

        foreach ($argsFormat->getCommandOptions() as $commandOption) {
            $nameParts[] = $commandOption->isLongNamePreferred()
                ? '--'.$commandOption->getLongName()
                : '-'.$commandOption->getShortName();
        }

        if ($lastCommandOptional) {
            $lastIndex = count($nameParts) - 1;
            $nameParts[$lastIndex] = '['.$nameParts[$lastIndex].']';
        }

        foreach ($argsFormat->getOptions($includeBaseOptions) as $option) {
            // \xC2\xA0 is a non-breaking space
            if ($option->isValueRequired()) {
                $format = "%s\xC2\xA0<%s>";
            } elseif ($option->isValueOptional()) {
                $format = "%s\xC2\xA0[<%s>]";
            } else {
                $format = '%s';
            }

            $optionName = $option->isLongNamePreferred()
                ? '--'.$option->getLongName()
                : '-'.$option->getShortName();

            $argumentParts[] = sprintf('['.$format.']', $optionName, $option->getValueName());
        }

        foreach ($argsFormat->getArguments() as $argument) {
            $argName = $argument->getName();

            $argumentParts[] = sprintf(
                $argument->isRequired() ? '<%s>' : '[<%s>]',
                $argName.($argument->isMultiValued() ? '1' : '')
            );

            if ($argument->isMultiValued()) {
                $argumentParts[] = sprintf('... [<%sN>]', $argName);
            }
        }

        $argsOpts = implode(' ', $argumentParts);
        $name = implode(' ', $nameParts);

        $this->layout->add(new LabeledParagraph($prefix.$name, $argsOpts, 1, false));
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

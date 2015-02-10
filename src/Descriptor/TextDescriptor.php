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
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputDefinitionBuilder;
use Webmozart\Console\Api\Input\InputOption;
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

        $definitionBuilder = new InputDefinitionBuilder($application->getBaseInputDefinition());
        $definitionBuilder->addArgument(new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'));
        $definitionBuilder->addArgument(new InputArgument('arg', InputArgument::MULTI_VALUED, 'The arguments of the command'));
        $inputDefinition = $definitionBuilder->getDefinition();
        $inputArgs = $inputDefinition->getArguments();
        $inputOpts = $inputDefinition->getOptions();

        $this->printApplicationName($application, $options);
        $this->printApplicationUsage($application, $inputDefinition, $options);

        if ($inputArgs) {
            $this->printInputArguments($inputArgs, $options);
        }

        if ($inputOpts) {
            $this->printGlobalInputOptions($inputOpts, $options);
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
        $inputDefinition = $command->getInputDefinition();
        $baseDefinition = $inputDefinition->getBaseDefinition();
        $inputArgs = $inputDefinition->getArguments();
        $inputOpts = $inputDefinition->getOptions(false);
        $baseOpts = $baseDefinition ? $baseDefinition->getOptions() : array();
        $subCommands = $command->getSubCommands();
        $optCommands = $command->getOptionCommands();

        $this->printCommandUsage($command, $options);

        if ($inputArgs) {
            $this->printInputArguments($inputArgs, $options);
        }

        if (!$subCommands->isEmpty() || !$optCommands->isEmpty()) {
            $this->printSubCommands($subCommands, $optCommands, $options);
        }

        if ($inputOpts) {
            $this->printInputOptions($inputOpts, $options);
        }

        if ($baseOpts) {
            $this->printGlobalInputOptions($baseOpts, $options);
        }

        if ($help) {
            $this->printCommandHelp($help, $options);
        }
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

        $this->layout->add(new Paragraph("<h>USAGE</h>"));
        $this->layout->beginBlock();

        $this->printSynopsis($inputDefinition, $executableName, '', true);

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
        $executableName = $command->getApplication()->getConfig()->getExecutableName();
        $prefix = $command->hasSubCommands() || $command->hasOptionCommands() ? '    ' : '';
        $subCommands = $command->getSubCommands();
        $optionCommands = $command->getOptionCommands();
        $aliases = $command->getAliases();

        $this->layout->add(new Paragraph('<h>USAGE</h>'));
        $this->layout->beginBlock();

        if ($defaultSubCommand = $command->getDefaultSubCommand()) {
            $this->printSynopsis($defaultSubCommand->getInputDefinition(), $executableName, $prefix, false, true);
            unset($subCommands[$defaultSubCommand->getName()]);
        } elseif ($defaultOptionCommand = $command->getDefaultOptionCommand()) {
            $this->printSynopsis($defaultOptionCommand->getInputDefinition(), $executableName, $prefix, false, true);
            unset($optionCommands[$defaultOptionCommand->getName()]);
        } else {
            $this->printSynopsis($command->getInputDefinition(), $executableName, $prefix);
        }

        foreach ($subCommands as $subCommand) {
            $this->printSynopsis($subCommand->getInputDefinition(), $executableName, 'or: ');
        }

        foreach ($optionCommands as $optionCommand) {
            $this->printSynopsis($optionCommand->getInputDefinition(), $executableName, 'or: ');
        }

        if ($aliases) {
            $this->layout->add(new EmptyLine());
            $this->printAliases($aliases, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints a list of input arguments.
     *
     * @param InputArgument[] $inputArgs The input arguments to describe.
     * @param array           $options   Additional options.
     *
     * @return string The output.
     */
    protected function printInputArguments(array $inputArgs, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>ARGUMENTS</h>'));
        $this->layout->beginBlock();

        foreach ($inputArgs as $argument) {
            $this->printInputArgument($argument, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints an input argument.
     *
     * @param InputArgument $argument The input argument to describe.
     * @param array         $options  Additional options.
     *
     * @return string The output.
     */
    protected function printInputArgument(InputArgument $argument, array $options = array())
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
     * Prints a list of input options.
     *
     * @param InputOption[] $inputOpts The input options to describe.
     * @param array         $options   Additional options.
     *
     * @return string The output.
     */
    protected function printInputOptions($inputOpts, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>OPTIONS</h>'));
        $this->layout->beginBlock();

        foreach ($inputOpts as $option) {
            $this->printInputOption($option, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints a list of global input options.
     *
     * @param InputOption[] $inputOpts The input options to describe.
     * @param array         $options   Additional options.
     *
     * @return string The output.
     */
    protected function printGlobalInputOptions($inputOpts, array $options = array())
    {
        $this->layout->add(new Paragraph('<h>GLOBAL OPTIONS</h>'));
        $this->layout->beginBlock();

        foreach ($inputOpts as $option) {
            $this->printInputOption($option, $options);
        }

        $this->layout->endBlock();
        $this->layout->add(new EmptyLine());
    }

    /**
     * Prints an input option.
     *
     * @param InputOption $option  The input option to describe.
     * @param array       $options Additional options.
     */
    protected function printInputOption(InputOption $option, array $options = array())
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

    protected function printSubCommand(Command $command, array $options = array())
    {
        $config = $command->getConfig();
        $description = $config->getDescription();
        $help = $config->getHelp();
        $inputArgs = $command->getInputDefinition()->getArguments(false);
        $inputOpts = $command->getInputDefinition()->getOptions(false);

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
            $this->printInputArgument($argument, $options);
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

        if ('UNKNOWN' !== $config->getName() && 'UNKNOWN' !== $config->getVersion()) {
            $this->layout->add(new Paragraph("<info>{$config->getName()}</info> version <comment>{$config->getVersion()}</comment>"));
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
     * @param Command $command The command to describe.
     * @param array   $options Additional options.
     */
    protected function printCommand(Command $command, array $options = array())
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

    protected function printSynopsis(InputDefinition $inputDefinition, $executableName, $prefix = '', $includeBaseOptions = false, $lastCommandOptional = false)
    {
        $nameParts = array();
        $argumentParts = array();

        $nameParts[] = '<tt>'.$executableName.'</tt>';

        foreach ($inputDefinition->getCommandNames() as $commandName) {
            $nameParts[] = '<tt>'.$commandName->toString().'</tt>';
        }

        foreach ($inputDefinition->getCommandOptions() as $commandOption) {
            $nameParts[] = $commandOption->isLongNamePreferred()
                ? '--'.$commandOption->getLongName()
                : '-'.$commandOption->getShortName();
        }

        if ($lastCommandOptional) {
            $lastIndex = count($nameParts) - 1;
            $nameParts[$lastIndex] = '['.$nameParts[$lastIndex].']';
        }

        foreach ($inputDefinition->getOptions($includeBaseOptions) as $option) {
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

        foreach ($inputDefinition->getArguments() as $argument) {
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

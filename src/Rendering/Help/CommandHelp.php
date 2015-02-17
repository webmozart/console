<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Help;

use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Rendering\Element\EmptyLine;
use Webmozart\Console\Rendering\Element\Paragraph;
use Webmozart\Console\Rendering\Layout\BlockLayout;

/**
 * Renders the help of a console command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandHelp extends AbstractHelp
{
    /**
     * @var Command
     */
    private $command;

    /**
     * Creates the help.
     *
     * @param Command $command The command to render.
     */
    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderHelp(BlockLayout $layout)
    {
        $help = $this->command->getConfig()->getHelp();
        $argsFormat = $this->command->getArgsFormat();
        $subCommands = $this->command->getSubCommands();
        $optCommands = $this->command->getOptionCommands();

        $this->renderUsage($layout, $this->command);

        if ($argsFormat->hasArguments()) {
            $this->renderArguments($layout, $argsFormat->getArguments());
        }

        if (!$subCommands->isEmpty() || !$optCommands->isEmpty()) {
            $this->renderSubCommands($layout, $subCommands, $optCommands);
        }

        if ($argsFormat->hasOptions(false)) {
            $this->renderOptions($layout, $argsFormat->getOptions(false));
        }

        if ($argsFormat->getBaseFormat() && $argsFormat->getBaseFormat()->hasOptions()) {
            $this->renderGlobalOptions($layout, $argsFormat->getBaseFormat()->getOptions());
        }

        if ($help) {
            $this->renderDescription($layout, $help);
        }
    }

    /**
     * Renders the "Usage" section.
     *
     * @param BlockLayout $layout  The layout.
     * @param Command     $command The command to render.
     */
    protected function renderUsage(BlockLayout $layout, Command $command)
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

        $layout->add(new Paragraph('<h>USAGE</h>'));
        $layout->beginBlock();

        foreach ($formatsToPrint as $vars) {
            $this->renderSynopsis($layout, $vars[0], $appName, $prefix, $vars[1]);
            $prefix = 'or: ';
        }

        if ($command instanceof NamedCommand && $command->hasAliases()) {
            $layout->add(new EmptyLine());
            $this->renderAliases($layout, $command->getAliases());
        }

        $layout->endBlock();
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the aliases of a command.
     *
     * @param BlockLayout $layout  The layout.
     * @param string[]    $aliases The aliases to render.
     */
    protected function renderAliases(BlockLayout $layout, $aliases)
    {
        $layout->add(new Paragraph('aliases: '.implode(', ', $aliases)));
    }

    /**
     * Renders the "Commands" section.
     *
     * @param BlockLayout       $layout         The layout.
     * @param CommandCollection $subCommands    The sub-commands to render.
     * @param CommandCollection $optionCommands The option commands to render.
     */
    protected function renderSubCommands(BlockLayout $layout, CommandCollection $subCommands, CommandCollection $optionCommands)
    {
        $layout->add(new Paragraph('<h>COMMANDS</h>'));
        $layout->beginBlock();

        foreach ($subCommands as $subCommand) {
            $this->renderSubCommand($layout, $subCommand);
        }

        foreach ($optionCommands as $optionCommand) {
            $this->renderSubCommand($layout, $optionCommand);
        }

        $layout->endBlock();
    }

    /**
     * Renders a sub-command in the "Commands" section.
     *
     * @param BlockLayout  $layout  The layout.
     * @param NamedCommand $command The command to render.
     */
    protected function renderSubCommand(BlockLayout $layout, NamedCommand $command)
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
                $name .= ' ('.$alternativeName.')';
            }
        } else {
            $name = $command->getName();
        }

        $layout->add(new Paragraph("<tt>$name</tt>"));
        $layout->beginBlock();

        if ($description) {
            $this->renderSubCommandDescription($layout, $description);
        }

        if ($help) {
            $this->renderSubCommandHelp($layout, $help);
        }

        if ($inputArgs) {
            $this->renderSubCommandArguments($layout, $inputArgs);
        }

        if ($inputOpts) {
            $this->renderSubCommandOptions($layout, $inputOpts);
        }

        $layout->endBlock();
    }

    /**
     * Renders the description of a sub-command.
     *
     * @param BlockLayout $layout      The layout.
     * @param string      $description The description.
     */
    protected function renderSubCommandDescription(BlockLayout $layout, $description)
    {
        $layout->add(new Paragraph($description));
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the help text of a sub-command.
     *
     * @param BlockLayout $layout The layout.
     * @param string      $help   The help text.
     */
    protected function renderSubCommandHelp(BlockLayout $layout, $help)
    {
        $layout->add(new Paragraph($help));
        $layout->add(new EmptyLine());
    }

    /**
     * Renders the argument descriptions of a sub-command.
     *
     * @param BlockLayout $layout    The layout.
     * @param Argument[]  $arguments The arguments.
     */
    protected function renderSubCommandArguments(BlockLayout $layout, array $arguments)
    {
        foreach ($arguments as $argument) {
            $this->renderArgument($layout, $argument);
        }

        $layout->add(new EmptyLine());
    }

    /**
     * Renders the option descriptions of a sub-command.
     *
     * @param BlockLayout $layout  The layout.
     * @param Option[]    $options The options.
     */
    protected function renderSubCommandOptions(BlockLayout $layout, array $options)
    {
        foreach ($options as $option) {
            $this->renderOption($layout, $option);
        }

        $layout->add(new EmptyLine());
    }

    /**
     * Renders the "Description" section.
     *
     * @param BlockLayout $layout The layout.
     * @param string      $help   The help text.
     */
    protected function renderDescription(BlockLayout $layout, $help)
    {
        $layout
            ->add(new Paragraph('<h>DESCRIPTION</h>'))
            ->beginBlock()
            ->add(new Paragraph($help))
            ->endBlock()
            ->add(new EmptyLine())
        ;
    }
}

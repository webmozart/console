<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Command;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\CommandConfig;
use Webmozart\Console\Api\Command\OptionCommandConfig;
use Webmozart\Console\Api\Command\SubCommandConfig;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputOption;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandTest extends PHPUnit_Framework_TestCase
{
    public function testCreateCommand()
    {
        $config = new CommandConfig('command');
        $config->addArgument('argument');
        $config->addOption('option', 'o');
        $config->addSubCommandConfig($addConfig = new SubCommandConfig('add'));
        $config->addOptionCommandConfig($deleteConfig = new OptionCommandConfig('delete', 'd'));

        $baseDefinition = new InputDefinition(array(
            new InputOption('verbose', 'v'),
        ));

        $command = new Command($config, $baseDefinition);
        $inputDefinition = $command->getInputDefinition();

        $this->assertSame($config, $command->getConfig());
        $this->assertSame($baseDefinition, $inputDefinition->getBaseDefinition());
        $this->assertCount(1, $inputDefinition->getArguments());
        $this->assertTrue($inputDefinition->hasArgument('argument'));
        $this->assertCount(2, $inputDefinition->getOptions());
        $this->assertTrue($inputDefinition->hasOption('option'));
        $this->assertTrue($inputDefinition->hasOption('verbose'));

        $this->assertEquals(new CommandCollection(array(
            'add' => new Command($addConfig, $inputDefinition),
        )), $command->getSubCommands());

        $this->assertEquals(new CommandCollection(array(
            'delete' => new Command($deleteConfig, $inputDefinition),
        )), $command->getOptionCommands());
    }

    /**
     * @expectedException \LogicException
     */
    public function testCreateFailsIfNoName()
    {
        new Command(new CommandConfig());
    }

    public function testGetSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig($subConfig = new SubCommandConfig('sub'));
        $command = new Command($config);
        $inputDefinition = $command->getInputDefinition();

        $this->assertEquals(new Command($subConfig, $inputDefinition), $command->getSubCommand('sub'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foo
     */
    public function testGetSubCommandFailsIfNotFound()
    {
        $command = new Command(new CommandConfig('command'));

        $command->getSubCommand('foo');
    }

    public function testHasSubCommand()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $command = new Command($config);

        $this->assertTrue($command->hasSubCommand('sub'));
        $this->assertFalse($command->hasSubCommand('foo'));
    }

    public function testHasSubCommands()
    {
        $config = new CommandConfig('command');
        $config->addSubCommandConfig(new SubCommandConfig('sub'));
        $command = new Command($config);

        $this->assertTrue($command->hasSubCommands());
    }

    public function testHasNoSubCommands()
    {
        $command = new Command(new CommandConfig('command'));

        $this->assertFalse($command->hasSubCommands());
    }

    public function testGetOptionCommand()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig($optionConfig = new OptionCommandConfig('option'));
        $command = new Command($config);
        $inputDefinition = $command->getInputDefinition();

        $this->assertEquals(new Command($optionConfig, $inputDefinition), $command->getOptionCommand('option'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foo
     */
    public function testGetOptionCommandFailsIfNotFound()
    {
        $command = new Command(new CommandConfig('command'));

        $command->getOptionCommand('foo');
    }

    public function testHasOptionCommand()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option'));
        $command = new Command($config);

        $this->assertTrue($command->hasOptionCommand('option'));
        $this->assertFalse($command->hasOptionCommand('foo'));
    }

    public function testHasOptionCommands()
    {
        $config = new CommandConfig('command');
        $config->addOptionCommandConfig(new OptionCommandConfig('option'));
        $command = new Command($config);

        $this->assertTrue($command->hasOptionCommands());
    }

    public function testHasNoOptionCommands()
    {
        $command = new Command(new CommandConfig('command'));

        $this->assertFalse($command->hasOptionCommands());
    }
}

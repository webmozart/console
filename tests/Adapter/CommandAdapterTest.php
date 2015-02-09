<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Adapter;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Helper\HelperSet;
use Webmozart\Console\Adapter\ApplicationAdapter;
use Webmozart\Console\Adapter\CommandAdapter;
use Webmozart\Console\Adapter\InputDefinitionAdapter;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\ConsoleApplication;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $config = CommandConfig::create()
            ->setName('command')
            ->addAlias('alias1')
            ->addAlias('alias2')
            ->setDescription('Description of the command')
            ->setHelp('The help for %command.name%')
            ->addArgument('argument')
            ->addOption('option', 'o')
            ->setHelperSet($helperSet = new HelperSet())
        ;

        $applicationConfig = new ApplicationConfig();
        $application = new ConsoleApplication($applicationConfig);
        $applicationAdapter = new ApplicationAdapter($application);

        $command = new Command($config, null, $application);
        $adapter = new CommandAdapter($command, $applicationAdapter);

        $this->assertSame('command', $adapter->getName());
        $this->assertEquals(new InputDefinitionAdapter($command->getInputDefinition()), $adapter->getDefinition());
        $this->assertEquals(new InputDefinitionAdapter($command->getInputDefinition()), $adapter->getNativeDefinition());
        $this->assertSame($command, $adapter->getAdaptedCommand());
        $this->assertSame(array('alias1', 'alias2'), $adapter->getAliases());
        $this->assertSame($applicationAdapter, $adapter->getApplication());
        $this->assertSame('Description of the command', $adapter->getDescription());
        $this->assertSame('The help for %command.name%', $adapter->getHelp());
        $this->assertSame('The help for command', $adapter->getProcessedHelp());
        $this->assertSame($helperSet, $adapter->getHelperSet());
        $this->assertSame('command [-o|--option] cmd1 [argument]', $adapter->getSynopsis());
        $this->assertTrue($adapter->isEnabled());
    }

    public function testCreateDisabled()
    {
        $config = CommandConfig::create()
            ->setName('command')
            ->disable()
        ;

        $applicationConfig = new ApplicationConfig();
        $application = new ConsoleApplication($applicationConfig);
        $applicationAdapter = new ApplicationAdapter($application);

        $command = new Command($config, null, $application);
        $adapter = new CommandAdapter($command, $applicationAdapter);

        $this->assertFalse($adapter->isEnabled());
    }
}

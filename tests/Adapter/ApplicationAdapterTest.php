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
use Webmozart\Console\Adapter\ArgsFormatAdapter;
use Webmozart\Console\Adapter\CommandAdapter;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\ConsoleApplication;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationAdapterTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->setDisplayName('Test Name')
            ->setVersion('1.2.3')
            ->setHelperSet($helperSet = new HelperSet())
            ->setOutputDimensions(new Dimensions(80, 20))
            ->beginCommand('command')->end()
        ;

        $application = new ConsoleApplication($config);
        $adapter = new ApplicationAdapter($application);

        $this->assertSame('Test Name', $adapter->getName());
        $this->assertSame('1.2.3', $adapter->getVersion());
        $this->assertSame('<info>Test Name</info> version <comment>1.2.3</comment>', $adapter->getLongVersion());
        $this->assertSame('<info>Test Name</info> version <comment>1.2.3</comment>', $adapter->getHelp());
        $this->assertSame($helperSet, $adapter->getHelperSet());
        $this->assertSame(array(80, 20), $adapter->getTerminalDimensions());
        $this->assertSame(array(), $adapter->getNamespaces());
        $this->assertEquals(new ArgsFormatAdapter($application->getGlobalArgsFormat()), $adapter->getDefinition());

        $commandAdapter = new CommandAdapter($application->getCommand('command'), $adapter);
        $commandAdapter->setApplication($adapter);
        $commandAdapter->setHelperSet($helperSet);

        $this->assertEquals($commandAdapter, $adapter->get('command'));
    }
}

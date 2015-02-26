<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Rendering\Help;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\Rendering\Help\ApplicationHelp;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ApplicationHelpTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var Canvas
     */
    private $canvas;

    protected function setUp()
    {
        $this->io = new BufferedIO();
        $this->canvas = new Canvas($this->io, new Dimensions(80, 20));
        $this->canvas->setFlushOnWrite(true);
    }

    public function testRender()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->setDisplayName('The Application')
            ->addArgument('global-argument', 0, 'Description of "global-argument"')
            ->addOption('global-option', null, 0, 'Description of "global-option"')
            ->beginCommand('command1')
                ->setDescription('Description of "command1"')
            ->end()
            ->beginCommand('command2')
                ->setDescription('Description of "command2"')
            ->end()
            ->beginCommand('longer-command3')
                ->setDescription('Description of "longer-command3"')
            ->end()
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
The Application

USAGE
  test-bin [--global-option] <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>        The command to execute
  <arg>            The arguments of the command

GLOBAL OPTIONS
  --global-option  Description of "global-option"

AVAILABLE COMMANDS
  command1         Description of "command1"
  command2         Description of "command2"
  longer-command3  Description of "longer-command3"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testSortCommands()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->setDisplayName('The Application')
            ->beginCommand('command3')->end()
            ->beginCommand('command1')->end()
            ->beginCommand('command2')->end()
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
The Application

USAGE
  test-bin <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>  The command to execute
  <arg>      The arguments of the command

AVAILABLE COMMANDS
  command1
  command2
  command3


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderVersion()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->setDisplayName('The Application')
            ->setVersion('1.2.3')
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
The Application version 1.2.3

USAGE
  test-bin <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>  The command to execute
  <arg>      The arguments of the command


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderDefaultDisplayName()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
Test Bin

USAGE
  test-bin <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>  The command to execute
  <arg>      The arguments of the command


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderNoName()
    {
        $config = ApplicationConfig::create();

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
Console Tool

USAGE
  console <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>  The command to execute
  <arg>      The arguments of the command


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderGlobalOptionWithPreferredShortName()
    {
        $config = ApplicationConfig::create()
            ->addOption('global-option', 'g', Option::PREFER_SHORT_NAME, 'Description of "global-option"')
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
Console Tool

USAGE
  console [-g] <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>             The command to execute
  <arg>                 The arguments of the command

GLOBAL OPTIONS
  -g (--global-option)  Description of "global-option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderGlobalOptionWithPreferredLongName()
    {
        $config = ApplicationConfig::create()
            ->addOption('global-option', 'g', Option::PREFER_LONG_NAME, 'Description of "global-option"')
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
Console Tool

USAGE
  console [--global-option] <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>             The command to execute
  <arg>                 The arguments of the command

GLOBAL OPTIONS
  --global-option (-g)  Description of "global-option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderDescription()
    {
        $config = ApplicationConfig::create()
            ->setHelp('The help')
        ;

        $application = new ConsoleApplication($config);
        $help = new ApplicationHelp($application);
        $help->render($this->canvas);

        $expected = <<<EOF
Console Tool

USAGE
  console <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>  The command to execute
  <arg>      The arguments of the command

DESCRIPTION
  The help


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

}

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
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\Rendering\Help\CommandHelp;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandHelpTest extends PHPUnit_Framework_TestCase
{
    /**
     * Non-breaking space.
     */
    private $nbsp = "\xC2\xA0";

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
            ->addArgument('global-argument', 0, 'Description of "global-argument"')
            ->addOption('global-option', null, 0, 'Description of "global-option"')
            ->beginCommand('command')
                ->setDescription('Description of "command"')
                ->setHelp('Help of "command"')
                ->addAlias('command-alias')
                ->addArgument('argument', 0, 'Description of "argument"')
                ->addOption('option', null, 0, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [--option] [<global-argument>] [<argument>]

  aliases: command-alias

ARGUMENTS
  <global-argument>  Description of "global-argument"
  <argument>         Description of "argument"

OPTIONS
  --option           Description of "option"

GLOBAL OPTIONS
  --global-option    Description of "global-option"

DESCRIPTION
  Help of "command"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderRequiredArgument()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addArgument('argument', Argument::REQUIRED, 'Description of "argument"')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command <argument>

ARGUMENTS
  <argument>  Description of "argument"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionWithOptionalValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', null, Option::OPTIONAL_VALUE, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<...>]]

OPTIONS
  --option  Description of "option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionWithOptionalValueShortNamePreferred()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::OPTIONAL_VALUE, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [-o{$this->nbsp}[<...>]]

OPTIONS
  -o (--option)  Description of "option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionWithOptionalValueLongNamePreferred()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::OPTIONAL_VALUE | Option::PREFER_LONG_NAME, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<...>]]

OPTIONS
  --option (-o)  Description of "option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionWithRequiredValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', null, Option::REQUIRED_VALUE, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}<...>]

OPTIONS
  --option  Description of "option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionWithDefaultValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', null, Option::OPTIONAL_VALUE, 'Description of "option"', 'Default')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<...>]]

OPTIONS
  --option  Description of "option" (default: "Default")


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionWithNamedValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', null, Option::OPTIONAL_VALUE, 'Description of "option"', null, 'value')
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<value>]]

OPTIONS
  --option  Description of "option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderCommandWithSubCommands()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->addOption('global-option', 'g', 0, 'Description of "global-option"')
            ->beginCommand('command')
                ->addArgument('argument', 0, 'Description of "argument"')
                ->addOption('option', null, 0, 'Description of "option"')
                ->beginSubCommand('add')
                    ->setDescription('Description of "add"')
                    ->addArgument('sub-argument1', 0, 'Description of "sub-argument1"')
                    ->addArgument('sub-argument2', 0, 'Description of "sub-argument2"')
                    ->addOption('sub-option1', 'o', 0, 'Description of "sub-option1"')
                    ->addOption('sub-option2', null, 0, 'Description of "sub-option2"')
                ->end()
                ->beginSubCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command [--option] [<argument>]
  or: test-bin command add [-o] [--sub-option2] [<argument>] [<sub-argument1>]
                           [<sub-argument2>]
  or: test-bin command delete [<argument>]

ARGUMENTS
  <argument>            Description of "argument"

COMMANDS
  add
    Description of "add"

    <sub-argument1>     Description of "sub-argument1"
    <sub-argument2>     Description of "sub-argument2"

    -o (--sub-option1)  Description of "sub-option1"
    --sub-option2       Description of "sub-option2"

  delete
    Description of "delete"

OPTIONS
  --option              Description of "option"

GLOBAL OPTIONS
  -g (--global-option)  Description of "global-option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testSortSubCommands()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->beginSubCommand('sub3')->end()
                ->beginSubCommand('sub1')->end()
                ->beginSubCommand('sub2')->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command
  or: test-bin command sub3
  or: test-bin command sub1
  or: test-bin command sub2

COMMANDS
  sub1

  sub2

  sub3


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderCommandWithDefaultSubCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->beginSubCommand('add')
                    ->markDefault()
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginSubCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command [add] [<argument>]
  or: test-bin command delete

COMMANDS
  add
    Description of "add"

    <argument>  Description of "argument"

  delete
    Description of "delete"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderCommandWithAnonymousSubCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->beginSubCommand('add')
                    ->markAnonymous()
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginSubCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command [<argument>]
  or: test-bin command delete

COMMANDS
  delete
    Description of "delete"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderCommandWithOptionCommands()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->addOption('global-option', 'g', 0, 'Description of "global-option"')
            ->beginCommand('command')
                ->addArgument('argument', 0, 'Description of "argument"')
                ->addOption('option', null, 0, 'Description of "option"')
                ->beginOptionCommand('add', 'a')
                    ->setDescription('Description of "add"')
                    ->addArgument('sub-argument1', 0, 'Description of "sub-argument1"')
                    ->addArgument('sub-argument2', 0, 'Description of "sub-argument2"')
                    ->addOption('sub-option1', 'o', 0, 'Description of "sub-option1"')
                    ->addOption('sub-option2', null, 0, 'Description of "sub-option2"')
                ->end()
                ->beginOptionCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command [--option] [<argument>]
  or: test-bin command -a [-o] [--sub-option2] [<argument>] [<sub-argument1>]
                          [<sub-argument2>]
  or: test-bin command --delete [<argument>]

ARGUMENTS
  <argument>            Description of "argument"

COMMANDS
  -a (--add)
    Description of "add"

    <sub-argument1>     Description of "sub-argument1"
    <sub-argument2>     Description of "sub-argument2"

    -o (--sub-option1)  Description of "sub-option1"
    --sub-option2       Description of "sub-option2"

  --delete
    Description of "delete"

OPTIONS
  --option              Description of "option"

GLOBAL OPTIONS
  -g (--global-option)  Description of "global-option"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderCommandWithDefaultOptionCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->beginOptionCommand('add', 'a')
                    ->markDefault()
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginOptionCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command [-a] [<argument>]
  or: test-bin command --delete

COMMANDS
  -a (--add)
    Description of "add"

    <argument>  Description of "argument"

  --delete
    Description of "delete"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderCommandWithAnonymousOptionCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->beginOptionCommand('add', 'a')
                    ->markAnonymous()
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginOptionCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command [<argument>]
  or: test-bin command --delete

COMMANDS
  --delete
    Description of "delete"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderOptionCommandWithPreferredLongName()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->beginOptionCommand('add', 'a')
                    ->setDescription('Description of "add"')
                    ->setPreferLongName()
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $help = new CommandHelp($application->getCommand('command'));
        $help->render($this->canvas);

        $expected = <<<EOF
USAGE
      test-bin command
  or: test-bin command --add

COMMANDS
  --add (-a)
    Description of "add"


EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }
}

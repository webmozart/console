<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Descriptor;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Descriptor\TextDescriptor;
use Webmozart\Console\Style\DefaultStyleSet;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TextDescriptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Non-breaking space.
     */
    private $nbsp = "\xC2\xA0";

    /**
     * @var TextDescriptor
     */
    private $descriptor;

    /**
     * @var BufferedOutput
     */
    private $buffer;

    /**
     * @var Output
     */
    private $output;

    protected function setUp()
    {
        $this->descriptor = new TextDescriptor();
        $this->buffer = new BufferedOutput();
        $this->output = new OutputInterfaceAdapter($this->buffer, new Dimensions(80, 20));
        $this->output->setDecorated(false);
        $this->output->setStyleSet(new DefaultStyleSet());
    }

    public function testDescribeCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->addArgument('global-argument', 0, 'Description of "global-argument"')
            ->addOption('global-option', 'g', 0, 'Description of "global-option"')
            ->beginCommand('command')
                ->setDescription('Description of "command"')
                ->setHelp('Help of "command"')
                ->addAlias('command-alias')
                ->addArgument('argument', 0, 'Description of "argument"')
                ->addOption('option', 'o', 0, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command [--option] [<global-argument>] [<argument>]

  aliases: command-alias

ARGUMENTS
  <global-argument>     Description of "global-argument"
  <argument>            Description of "argument"

OPTIONS
  --option (-o)         Description of "option"

GLOBAL OPTIONS
  --global-option (-g)  Description of "global-option"

DESCRIPTION
  Help of "command"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeRequiredArgument()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addArgument('argument', Argument::REQUIRED, 'Description of "argument"')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command <argument>

ARGUMENTS
  <argument>  Description of "argument"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeOptionWithOptionalValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::OPTIONAL_VALUE, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<...>]]

OPTIONS
  --option (-o)  Description of "option"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeOptionWithRequiredValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::REQUIRED_VALUE, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}<...>]

OPTIONS
  --option (-o)  Description of "option"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeOptionWithDefaultValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::OPTIONAL_VALUE, 'Description of "option"', 'Default')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<...>]]

OPTIONS
  --option (-o)  Description of "option" (default: "Default")


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeOptionWithNamedValue()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::OPTIONAL_VALUE, 'Description of "option"', null, 'value')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command [--option{$this->nbsp}[<value>]]

OPTIONS
  --option (-o)  Description of "option"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeOptionWithPreferredShortName()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addOption('option', 'o', Option::PREFER_SHORT_NAME, 'Description of "option"')
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
  test-bin command [-o]

OPTIONS
  -o (--option)  Description of "option"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeCommandWithSubCommands()
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
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
      test-bin command [--option] [<argument>]
  or: test-bin command add [--sub-option1] [--sub-option2] [<argument>]
                           [<sub-argument1>] [<sub-argument2>]
  or: test-bin command delete [<argument>]

ARGUMENTS
  <argument>            Description of "argument"

COMMANDS
  add
    Description of "add"

    <sub-argument1>     Description of "sub-argument1"
    <sub-argument2>     Description of "sub-argument2"

    --sub-option1 (-o)  Description of "sub-option1"
    --sub-option2       Description of "sub-option2"

  delete
    Description of "delete"

OPTIONS
  --option              Description of "option"

GLOBAL OPTIONS
  --global-option (-g)  Description of "global-option"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeCommandWithDefaultSubCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addDefaultCommand('add')
                ->beginSubCommand('add')
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginSubCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

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

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeCommandWithOptionCommands()
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
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
      test-bin command [--option] [<argument>]
  or: test-bin command -a [--sub-option1] [--sub-option2] [<argument>]
                          [<sub-argument1>] [<sub-argument2>]
  or: test-bin command --delete [<argument>]

ARGUMENTS
  <argument>            Description of "argument"

COMMANDS
  -a, --add
    Description of "add"

    <sub-argument1>     Description of "sub-argument1"
    <sub-argument2>     Description of "sub-argument2"

    --sub-option1 (-o)  Description of "sub-option1"
    --sub-option2       Description of "sub-option2"

  --delete
    Description of "delete"

OPTIONS
  --option              Description of "option"

GLOBAL OPTIONS
  --global-option (-g)  Description of "global-option"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeCommandWithDefaultOptionCommand()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addDefaultCommand('add')
                ->beginOptionCommand('add', 'a')
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginOptionCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
      test-bin command [-a] [<argument>]
  or: test-bin command --delete

COMMANDS
  -a, --add
    Description of "add"

    <argument>  Description of "argument"

  --delete
    Description of "delete"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeOptionCommandWithPreferredLongName()
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
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
      test-bin command
  or: test-bin command --add

COMMANDS
  --add, -a
    Description of "add"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeCommandWithUnnamedCommands()
    {
        $config = ApplicationConfig::create()
            ->setName('test-bin')
            ->beginCommand('command')
                ->addDefaultCommand('add')
                ->addDefaultCommand('delete')
                ->beginUnnamedCommand()
                    ->addArgument('unnamed', Argument::REQUIRED, 'Description of "unnamed"')
                ->end()
                ->beginSubCommand('add')
                    ->setDescription('Description of "add"')
                    ->addArgument('argument', 0, 'Description of "argument"')
                ->end()
                ->beginOptionCommand('delete')
                    ->setDescription('Description of "delete"')
                ->end()
            ->end();

        $application = new ConsoleApplication($config);
        $command = $application->getCommand('command');

        $status = $this->descriptor->describe($this->output, $command);

        $expected = <<<EOF
USAGE
      test-bin command <unnamed>
  or: test-bin command [add] [<argument>]
  or: test-bin command [--delete]

COMMANDS
  add
    Description of "add"

    <argument>  Description of "argument"

  --delete
    Description of "delete"


EOF;

        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

}

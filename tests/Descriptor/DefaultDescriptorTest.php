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

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Adapter\InputDefinitionAdapter;
use Webmozart\Console\Adapter\InputInterfaceAdapter;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputOption;
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Descriptor\DefaultDescriptor;
use Webmozart\Console\Process\ProcessLauncher;
use Webmozart\Console\Style\DefaultStyleSet;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultDescriptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProcessLauncher
     */
    private $processLauncher;

    /**
     * @var DefaultDescriptor
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

    /**
     * @var InputDefinition
     */
    private $inputDefinition;

    protected function setUp()
    {
        $this->executableFinder = $this->getMockBuilder('Symfony\Component\Process\ExecutableFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processLauncher = $this->getMockBuilder('Webmozart\Console\Process\ProcessLauncher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->descriptor = new DefaultDescriptor($this->executableFinder, $this->processLauncher);
        $this->buffer = new BufferedOutput();
        $this->output = new OutputInterfaceAdapter($this->buffer, new Dimensions(80, 20));
        $this->output->setDecorated(false);
        $this->output->setStyleSet(new DefaultStyleSet());

        $this->inputDefinition = new InputDefinition(array(
            new InputOption('all'),
            new InputOption('man'),
            new InputOption('ascii-doc'),
            new InputOption('xml'),
            new InputOption('json'),
            new InputOption('text'),
            new InputOption('help', 'h'),
        ));
    }

    public function getInputForTextHelp()
    {
        return array(
            array('-h'),
            // "-h" overrides everything
            array('-h --xml'),
            array('--text'),
            array('--help --text'),
        );
    }

    public function getInputForXmlHelp()
    {
        return array(
            array('--xml'),
            array('--help --xml'),
        );
    }

    public function getInputForJsonHelp()
    {
        return array(
            array('--json'),
            array('--help --json'),
        );
    }

    public function getInputForManHelp()
    {
        return array(
            array('--help'),
            array('--man'),
            array('--help --man'),
        );
    }

    public function getInputForAsciiDocHelp()
    {
        return array(
            array('--ascii-doc'),
            array('--help --ascii-doc'),
        );
    }

    /**
     * @dataProvider getInputForTextHelp
     */
    public function testDescribeApplicationAsText($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertApplicationHelp($this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeApplicationByDefault()
    {
        $this->testDescribeApplicationAsText('');
    }

    /**
     * @dataProvider getInputForXmlHelp
     */
    public function testDescribeApplicationAsXml($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);
        $output = $this->buffer->fetch();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertContains('<symfony name="Test Application" version="1.0.0">', $output);
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForJsonHelp
     */
    public function testDescribeApplicationAsJson($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);
        $output = $this->buffer->fetch();

        $this->assertStringStartsWith('{"commands":[', $output);
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForManHelp
     */
    public function testDescribeApplicationAsMan($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
            'manDir' => __DIR__.'/Fixtures/man',
            'defaultPage' => 'application',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("man-binary -l '%s'", __DIR__.'/Fixtures/man/application.1');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getInputForAsciiDocHelp
     */
    public function testDescribeApplicationAsAsciiDoc($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'application',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/application.txt');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    public function testDescribeApplicationAsAsciiDocPrintsWhenLessNotFound()
    {
        $options = array(
            'input' => $this->getStringInput('--ascii-doc'),
            'manDir' => __DIR__.'/Fixtures/man',
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'application',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue(false));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame("Contents of application.txt\n", $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeApplicationAsAsciiDocPrintsWhenProcessLauncherNotSupported()
    {
        $options = array(
            'input' => $this->getStringInput('--ascii-doc'),
            'manDir' => __DIR__.'/Fixtures/man',
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'application',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(false));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame("Contents of application.txt\n", $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeApplicationAsAsciiDocWhenManBinaryNotFound()
    {
        $options = array(
            'input' => $this->getStringInput('--help'),
            'manDir' => __DIR__.'/Fixtures/man',
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'application',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue(false));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/application.txt');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    public function testDescribeApplicationAsAsciiDocWhenManPageNotFound()
    {
        $options = array(
            'input' => $this->getStringInput('--help'),
            'manDir' => __DIR__.'/Fixtures/man',
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'man-not-found',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/man-not-found.txt');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    public function testPrintAsciiDocWhenProcessLauncherNotSupported()
    {
        $options = array(
            'input' => $this->getStringInput('--help'),
            'manDir' => __DIR__.'/Fixtures/man',
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'application',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(false));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame("Contents of application.txt\n", $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeApplicationAsTextWhenAsciiDocPageNotFound()
    {
        $options = array(
            'input' => $this->getStringInput('--help'),
            'manDir' => __DIR__.'/Fixtures/man',
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'defaultPage' => 'not-found',
        );

        $object = $this->getApplication();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertApplicationHelp($this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForTextHelp
     */
    public function testDescribeCommandAsText($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $expected = <<<EOF
USAGE
  test-bin command1 [--option] [--value\xC2\xA0<...>] [<arg>]

  aliases: command1-alias

ARGUMENTS
  <arg>                  Description of the "arg" argument

OPTIONS
  --option (-o)          Description of the "option" option
  --value (-v)           Description of the "value" option

GLOBAL OPTIONS
  --help (-h)            Description of the "help" option
  --ansi                 Description of the "ansi" option
  --no-interaction (-n)  Description of the "no-interaction" option


EOF;


        $this->assertSame($expected, $this->buffer->fetch());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForXmlHelp
     */
    public function testDescribeCommandAsXml($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);

        $output = $this->buffer->fetch();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertContains('<command id="command1" name="command1">', $output);
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForJsonHelp
     */
    public function testDescribeCommandAsJson($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($this->output, $object, $options);
        $output = $this->buffer->fetch();

        $this->assertStringStartsWith('{"name":"command1",', $output);
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForManHelp
     */
    public function testDescribeCommandAsMan($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
            'manDir' => __DIR__.'/Fixtures/man',
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("man-binary -l '%s'", __DIR__.'/Fixtures/man/command1.1');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getInputForManHelp
     */
    public function testDescribeCommandAsManWithPrefix($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
            'manDir' => __DIR__.'/Fixtures/man',
            'commandPrefix' => 'prefix-',
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("man-binary -l '%s'", __DIR__.'/Fixtures/man/prefix-command1.1');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getInputForAsciiDocHelp
     */
    public function testDescribeCommandAsAsciiDoc($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/command1.txt');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getInputForAsciiDocHelp
     */
    public function testDescribeCommandAsAsciiDocWithPrefix($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString),
            'asciiDocDir' => __DIR__.'/Fixtures/ascii-doc',
            'commandPrefix' => 'prefix-',
        );

        $object = $this->getApplication()->getCommand('command1');

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/prefix-command1.txt');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $object, $options);

        $this->assertSame(123, $status);
    }

    private function assertApplicationHelp($string)
    {
        $expected = <<<EOF
Test Application version 1.0.0

USAGE
  test-bin [--help] [--ansi] [--no-interaction] <command> [<arg1>] ... [<argN>]

ARGUMENTS
  <command>              The command to execute
  <arg>                  The arguments of the command

GLOBAL OPTIONS
  --help (-h)            Description of the "help" option
  --ansi                 Description of the "ansi" option
  --no-interaction (-n)  Description of the "no-interaction" option

AVAILABLE COMMANDS
  command1               Description of command1
  command2               Description of command2


EOF;

        $this->assertSame($expected, $string);
    }

    private function getApplicationConfig()
    {
        return ApplicationConfig::create()
            ->setName('Test Application')
            ->setVersion('1.0.0')
            ->setExecutableName('test-bin')
            ->setOutputDimensions(new Dimensions(80, null))

            ->addOption('help', 'h', InputOption::VALUE_NONE, 'Description of the "help" option')
            ->addOption('ansi', null, InputOption::VALUE_NONE, 'Description of the "ansi" option')
            ->addOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Description of the "no-interaction" option')

            ->beginCommand('command1')
                ->addAlias('command1-alias')
                ->setDescription('Description of command1')
                ->addArgument('arg', InputArgument::OPTIONAL, 'Description of the "arg" argument')
                ->addOption('option', 'o', InputOption::VALUE_NONE, 'Description of the "option" option')
                ->addOption('value', 'v', InputOption::VALUE_REQUIRED, 'Description of the "value" option')
            ->end()

            ->beginCommand('command2')
                ->setDescription('Description of command2')

                ->beginSubCommand('list')
                    ->addArgument('arg', InputArgument::OPTIONAL)
                ->end()

                ->beginSubCommand('add')
                    ->addArgument('arg', InputArgument::REQUIRED)
                ->end()

                ->beginOptionCommand('delete', 'd')
                    ->addArgument('arg', InputArgument::REQUIRED)
                ->end()
            ->end()
        ;
    }

    public function getApplication(ApplicationConfig $config = null)
    {
        return new ConsoleApplication($config ?: $this->getApplicationConfig());
    }

    private function getStringInput($inputString)
    {
        $input = new InputInterfaceAdapter(new StringInput($inputString));
        $input->bind(new InputDefinitionAdapter($this->inputDefinition));

        return $input;
    }
}

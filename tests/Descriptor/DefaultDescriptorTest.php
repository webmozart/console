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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Descriptor\DefaultDescriptor;
use Webmozart\Console\Process\ProcessLauncher;
use Webmozart\Console\Style\NeptunStyle;
use Webmozart\Console\Tests\Fixtures\TestApplication;
use Webmozart\Console\Tests\Fixtures\TestPackageAddCommand;
use Webmozart\Console\Tests\Fixtures\TestPackageCommand;
use Webmozart\Console\Tests\Fixtures\TestSynopsisCommand;

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
        $this->output = new BufferedOutput();
        NeptunStyle::addStyles($this->output->getFormatter());
        $this->inputDefinition = new InputDefinition(array(
            new InputArgument('command'),
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

        $object = $this->createApplication();

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

        $this->assertApplicationHelp($this->output->fetch());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getInputForTextHelp
     */
    public function testDescribeApplicationAsTextWithCompositeCommands($inputString)
    {
        $options = array(
            'input' => $this->getStringInput($inputString.' --all'),
            'printCompositeCommands' => true,
        );

        $object = $this->createApplication();

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
Test Application version 1.0.0

USAGE
  test-bin [--help] [--quiet] [--verbose] [--version] [--ansi] [--no-ansi]
           [--no-interaction] <command> [<sub-command>] [<arg1>] ... [<argN>]

ARGUMENTS
  <command>              The command to execute
  <sub-command>          The sub-command to execute
  <arg>                  The arguments of the command

OPTIONS
  --help (-h)            Description
  --quiet (-q)           Description
  --verbose              Description
  --version (-V)         Description
  --ansi                 Description
  --no-ansi              Description
  --no-interaction (-n)  Description

AVAILABLE COMMANDS
  help                   Display the manual of a command
  pack                   Description of "pack"
  package                Description of "package"
  package add            Description of "package add"
  package addon          Description of "package addon"


EOF;

        $this->assertSame($expected, $this->output->fetch());
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

        $object = $this->createApplication();

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
        $output = $this->output->fetch();

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

        $object = $this->createApplication();

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
        $output = $this->output->fetch();

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

        $object = $this->createApplication();

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

        $object = $this->createApplication();

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

        $object = $this->createApplication();

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

        $this->assertSame("Contents of application.txt\n", $this->output->fetch());
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

        $object = $this->createApplication();

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

        $this->assertSame("Contents of application.txt\n", $this->output->fetch());
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

        $object = $this->createApplication();

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

        $object = $this->createApplication();

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

        $object = $this->createApplication();

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

        $this->assertSame("Contents of application.txt\n", $this->output->fetch());
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

        $object = $this->createApplication();

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

        $this->assertApplicationHelp($this->output->fetch());
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

        $object = $this->createCommand();

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
  test-bin package add [--option] [--value\xC2\xA0<...>] [<arg>]

  aliases: package add-alias

ARGUMENTS
  <arg>                  The "arg" argument

OPTIONS
  --option (-o)          The "option" option
  --value (-v)           The "value" option
  --help (-h)            Description
  --quiet (-q)           Description
  --verbose              Description
  --version (-V)         Description
  --ansi                 Description
  --no-ansi              Description
  --no-interaction (-n)  Description


EOF;


        $this->assertSame($expected, $this->output->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeCommandWithMultipleSynopsises()
    {
        $options = array(
            'input' => $this->getStringInput('-h'),
        );

        $object = new TestSynopsisCommand();
        $object->setApplication($this->createApplication());

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
      test-bin synopsis <arg>
  or: test-bin synopsis [--foo] [--bar]

ARGUMENTS
  <arg>                  The "arg" argument

OPTIONS
  --help (-h)            Description
  --quiet (-q)           Description
  --verbose              Description
  --version (-V)         Description
  --ansi                 Description
  --no-ansi              Description
  --no-interaction (-n)  Description


EOF;


        $this->assertSame($expected, $this->output->fetch());
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

        $object = new TestPackageAddCommand();

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

        $output = $this->output->fetch();

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $output);
        $this->assertContains('<command id="package add" name="package add">', $output);
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

        $object = new TestPackageAddCommand();

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
        $output = $this->output->fetch();

        $this->assertStringStartsWith('{"name":"package add",', $output);
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

        $object = new TestPackageAddCommand();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("man-binary -l '%s'", __DIR__.'/Fixtures/man/package-add.1');

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

        $object = new TestPackageCommand();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $command = sprintf("man-binary -l '%s'", __DIR__.'/Fixtures/man/prefix-package.1');

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

        $object = new TestPackageAddCommand();

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

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/package-add.txt');

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

        $object = new TestPackageCommand();

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

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/prefix-package.txt');

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
  test-bin [--help] [--quiet] [--verbose] [--version] [--ansi] [--no-ansi]
           [--no-interaction] <command> [<sub-command>] [<arg1>] ... [<argN>]

ARGUMENTS
  <command>              The command to execute
  <sub-command>          The sub-command to execute
  <arg>                  The arguments of the command

OPTIONS
  --help (-h)            Description
  --quiet (-q)           Description
  --verbose              Description
  --version (-V)         Description
  --ansi                 Description
  --no-ansi              Description
  --no-interaction (-n)  Description

AVAILABLE COMMANDS
  help                   Display the manual of a command
  pack                   Description of "pack"
  package                Description of "package"


EOF;

        $this->assertSame($expected, $string);
    }

    private function createApplication()
    {
        return new TestApplication(array(80, null));
    }

    /**
     * @return TestPackageAddCommand
     */
    protected function createCommand()
    {
        $object = new TestPackageAddCommand();
        $object->setApplication($this->createApplication());

        return $object;
    }

    private function getStringInput($inputString)
    {
        $input = new StringInput($inputString);
        $input->bind($this->inputDefinition);

        return $input;
    }
}

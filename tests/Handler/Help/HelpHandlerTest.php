<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Handler\Help;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Handler\Help\HelpHandler;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpHandlerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $manDir;

    /**
     * @var string
     */
    private $asciiDocDir;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var Command
     */
    private $command;

    /**
     * @var Command
     */
    private $helpCommand;

    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProcessLauncher
     */
    private $processLauncher;

    /**
     * @var HelpHandler
     */
    private $handler;

    protected function setUp()
    {
        $config = DefaultApplicationConfig::create()
            ->setName('the-app')
            ->setDisplayName('The Application')
            ->setVersion('1.2.3')
            ->beginCommand('the-command')->end()
        ;

        $this->manDir = __DIR__.'/Fixtures/man';
        $this->asciiDocDir = __DIR__.'/Fixtures/ascii-doc';
        $this->application = new ConsoleApplication($config);
        $this->command = $this->application->getCommand('the-command');
        $this->helpCommand = $this->application->getCommand('help');
        $this->io = new BufferedIO();
        $this->executableFinder = $this->getMockBuilder('Symfony\Component\Process\ExecutableFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processLauncher = $this->getMockBuilder('Webmozart\Console\Process\ProcessLauncher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->handler = new HelpHandler($this->executableFinder, $this->processLauncher);
        $this->handler->setManDir($this->manDir);
        $this->handler->setAsciiDocDir($this->asciiDocDir);
    }

    public function getArgsForTextHelp()
    {
        return array(
            array('-h'),
            // "-h" overrides everything
            array('-h --xml'),
            array('--text'),
            array('--help --text'),
        );
    }

    public function getArgsForXmlHelp()
    {
        return array(
            array('--xml'),
            array('--help --xml'),
        );
    }

    public function getArgsForJsonHelp()
    {
        return array(
            array('--json'),
            array('--help --json'),
        );
    }

    public function getArgsForManHelp()
    {
        return array(
            array('--help'),
            array('--man'),
            array('--help --man'),
        );
    }

    public function getArgsForAsciiDocHelp()
    {
        return array(
            array('--ascii-doc'),
            array('--help --ascii-doc'),
        );
    }

    /**
     * @dataProvider getArgsForTextHelp
     */
    public function testRenderCommandAsText($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
USAGE
  the-app the-command

GLOBAL OPTIONS
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getArgsForTextHelp
     */
    public function testRenderApplicationAsText($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
The Application version 1.2.3

USAGE
  the-app [-h] [-q] [-v [<level>]] [-V] [--ansi] [--no-ansi] [-n] <command>
          [<arg1>] ... [<argN>]

ARGUMENTS
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getArgsForXmlHelp
     */
    public function testRenderCommandXml($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<command id="the-command" name="the-command">
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getArgsForXmlHelp
     */
    public function testRenderApplicationXml($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<symfony name="The Application" version="1.2.3">
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getArgsForJsonHelp
     */
    public function testRenderCommandJson($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertStringStartsWith('{"name":"the-command",', $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getArgsForJsonHelp
     */
    public function testRenderApplicationJson($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertStringStartsWith('{"commands":[{"name":"help",', $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    /**
     * @dataProvider getArgsForManHelp
     */
    public function testRenderCommandMan($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('man-binary -l %path%', array(
                'path' => $this->manDir.'/the-command.1',
            ), false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForManHelp
     */
    public function testRenderCommandManWithPagePrefix($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('man-binary -l %path%', array(
                'path' => $this->manDir.'/prefix-the-command.1',
            ), false)
            ->will($this->returnValue(123));

        $this->handler->setCommandPagePrefix('prefix-');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForManHelp
     */
    public function testRenderApplicationMan($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('man-binary -l %path%', array(
                'path' => $this->manDir.'/the-app.1',
            ), false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForManHelp
     */
    public function testRenderApplicationManWithCustomPage($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('man-binary -l %path%', array(
                'path' => $this->manDir.'/custom-app.1',
            ), false)
            ->will($this->returnValue(123));

        $this->handler->setApplicationPage('custom-app');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForAsciiDocHelp
     */
    public function testRenderCommandAsciiDoc($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('less-binary %path%', array(
                'path' => $this->asciiDocDir.'/the-command.txt',
            ), false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForAsciiDocHelp
     */
    public function testRenderCommandAsciiDocWithPagePrefix($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));
        $args->setArgument('command', 'the-command');

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('less-binary %path%', array(
                'path' => $this->asciiDocDir.'/prefix-the-command.txt',
            ), false)
            ->will($this->returnValue(123));

        $this->handler->setCommandPagePrefix('prefix-');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForAsciiDocHelp
     */
    public function testRenderApplicationAsciiDoc($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $command = sprintf("less-binary '%s'", $this->asciiDocDir.'/the-app.txt');

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('less-binary %path%', array(
                'path' => $this->asciiDocDir.'/the-app.txt',
            ), false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getArgsForAsciiDocHelp
     */
    public function testRenderApplicationAsciiDocWithCustomPage($argString)
    {
        $args = $this->helpCommand->parseArgs(new StringArgs($argString));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('less-binary %path%', array(
                'path' => $this->asciiDocDir.'/custom-app.txt',
            ), false)
            ->will($this->returnValue(123));

        $this->handler->setApplicationPage('custom-app');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    public function testHelpLaunchesManByDefault()
    {
        $args = $this->helpCommand->parseArgs(new StringArgs('--help'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('man-binary -l %path%', array(
                'path' => $this->manDir.'/the-app.1',
            ), false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    public function testHelpLaunchesLessIfManBinaryNotFound()
    {
        $args = $this->helpCommand->parseArgs(new StringArgs('--help'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->at(0))
            ->method('find')
            ->with('man')
            ->will($this->returnValue(null));

        $this->executableFinder->expects($this->at(1))
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('less-binary %path%', array(
                'path' => $this->asciiDocDir.'/the-app.txt',
            ), false)
            ->will($this->returnValue(123));

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    public function testHelpLaunchesLessIfManPageNotFound()
    {
        $args = $this->helpCommand->parseArgs(new StringArgs('--help'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with('less-binary %path%', array(
                'path' => $this->asciiDocDir.'/man-not-found.txt',
            ), false)
            ->will($this->returnValue(123));

        $this->handler->setApplicationPage('man-not-found');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame(123, $status);
    }

    public function testHelpPrintsAsciiDocIfProcessLauncherNotSupported()
    {
        $args = $this->helpCommand->parseArgs(new StringArgs('--help'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(false));

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $this->assertSame("Contents of the-app.txt\n", $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }

    public function testHelpPrintsTextIfAsciiDocNotFound()
    {
        $args = $this->helpCommand->parseArgs(new StringArgs('--help'));

        $this->processLauncher->expects($this->any())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $this->handler->setApplicationPage('foobar');

        $status = $this->handler->handle($args, $this->io, $this->command);

        $expected = <<<EOF
The Application version 1.2.3

USAGE
  the-app
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchOutput());
        $this->assertSame(0, $status);
    }
}

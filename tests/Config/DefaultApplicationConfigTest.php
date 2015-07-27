<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Config;

use PHPUnit_Framework_Assert;
use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\Formatter\StyleSet;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Handler\CallbackHandler;
use Webmozart\Console\IO\FormattedIO;
use Webmozart\Console\IO\InputStream\StringInputStream;
use Webmozart\Console\IO\OutputStream\BufferedOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultApplicationConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultApplicationConfig
     */
    private $config;

    protected function setUp()
    {
        $this->config = new DefaultApplicationConfig();
        $this->config->setCatchExceptions(false);
        $this->config->setTerminateAfterRun(false);
    }

    /**
     * @dataProvider getApplicationHelpArgs
     */
    public function testRunApplicationHelp(RawArgs $args)
    {
        $this->config->beginCommand('command')->end();

        $application = new ConsoleApplication($this->config);
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(0, $status);
        $this->assertStringStartsWith('Console Tool', $output->fetch());
        $this->assertSame('', $errorOutput->fetch());
    }

    public function getApplicationHelpArgs()
    {
        return array(
            array(new StringArgs('')),
            array(new StringArgs('help')),
            array(new StringArgs('-h')),
            array(new StringArgs('--help')),
            array(new StringArgs('--help --foo')),
        );
    }

    /**
     * @dataProvider getCommandHelpArgs
     */
    public function testRunCommandHelp(RawArgs $args)
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    $io->writeLine('Command handled');
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(0, $status);
        $this->assertStringStartsWith("USAGE\n  console command", $output->fetch());
        $this->assertSame('', $errorOutput->fetch());
    }

    public function getCommandHelpArgs()
    {
        return array(
            array(new StringArgs('help command')),
            array(new StringArgs('command -h')),
            array(new StringArgs('command --help')),
            array(new StringArgs('-h command')),
            array(new StringArgs('--help command')),
            array(new StringArgs('--help command --foo')),
        );
    }

    public function testCreateAnsiFormatterIfOutputSupportsAnsi()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\Formatter\AnsiFormatter', $io->getFormatter());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command');
        $input = new StringInputStream();
        $output = $this->getMock('Webmozart\Console\Api\IO\OutputStream');
        $errorOutput = new BufferedOutputStream();

        $output->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(true);

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testCreatePlainFormatterIfOutputDoesNotSupportAnsi()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\Formatter\PlainFormatter', $io->getFormatter());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command');
        $input = new StringInputStream();
        $output = $this->getMock('Webmozart\Console\Api\IO\OutputStream');
        $errorOutput = new BufferedOutputStream();

        $output->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(false);

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testCreateAnsiFormatterIfAnsiOption()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\Formatter\AnsiFormatter', $io->getFormatter());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command --ansi');
        $input = new StringInputStream();
        $output = $this->getMock('Webmozart\Console\Api\IO\OutputStream');
        $errorOutput = new BufferedOutputStream();

        $output->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(false);

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testCreatePlainFormatterIfNoAnsiOption()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\Formatter\PlainFormatter', $io->getFormatter());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command --no-ansi');
        $input = new StringInputStream();
        $output = $this->getMock('Webmozart\Console\Api\IO\OutputStream');
        $errorOutput = new BufferedOutputStream();

        $output->expects($this->any())
            ->method('supportsAnsi')
            ->willReturn(true);

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testCreateStandardInputIfNonePassed()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\InputStream\StandardInputStream', $io->getInput());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command --no-ansi');
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, null, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testCreateStandardOutputIfNonePassed()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\OutputStream\StandardOutputStream', $io->getOutput());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command --no-ansi');
        $input = new StringInputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, null, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testCreateErrorOutputIfNonePassed()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    /* @var FormattedIO $io */
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\FormattedIO', $io);
                    PHPUnit_Framework_Assert::assertInstanceOf('Webmozart\Console\IO\OutputStream\ErrorOutputStream', $io->getErrorOutput());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command --no-ansi');
        $input = new StringInputStream();
        $output = new BufferedOutputStream();

        $status = $application->run($args, $input, $output);

        $this->assertSame(123, $status);
    }

    public function testSetVerbosityVerbose()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertTrue($io->isVerbose());
                    PHPUnit_Framework_Assert::assertFalse($io->isVeryVerbose());
                    PHPUnit_Framework_Assert::assertFalse($io->isDebug());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command -v');
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testSetVerbosityVeryVerbose()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertTrue($io->isVerbose());
                    PHPUnit_Framework_Assert::assertTrue($io->isVeryVerbose());
                    PHPUnit_Framework_Assert::assertFalse($io->isDebug());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command -vv');
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testSetVerbosityDebug()
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertTrue($io->isVerbose());
                    PHPUnit_Framework_Assert::assertTrue($io->isVeryVerbose());
                    PHPUnit_Framework_Assert::assertTrue($io->isDebug());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command -vvv');
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function testSetVerbosityDebugIfInDebugMode()
    {
        $this->config
            ->setDebug(true)

            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertTrue($io->isVerbose());
                    PHPUnit_Framework_Assert::assertTrue($io->isVeryVerbose());
                    PHPUnit_Framework_Assert::assertTrue($io->isDebug());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command');
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    /**
     * @dataProvider getQuietArgs
     */
    public function testSetQuiet($args)
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertTrue($io->isQuiet());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function getQuietArgs()
    {
        return array(
            array(new StringArgs('command -q')),
            array(new StringArgs('command --quiet')),
        );
    }

    /**
     * @dataProvider getNonInteractiveArgs
     */
    public function testSetNonInteractive($args)
    {
        $this->config
            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertFalse($io->isInteractive());

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }

    public function getNonInteractiveArgs()
    {
        return array(
            array(new StringArgs('command -n')),
            array(new StringArgs('command --no-interaction')),
        );
    }

    /**
     * @dataProvider getVersionArgs
     */
    public function testPrintVersion($args)
    {
        $this->config
            ->setDisplayName('The Application')
            ->setVersion('1.2.3')
            ->beginCommand('command')->end()
        ;

        $application = new ConsoleApplication($this->config);
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(0, $status);
        $this->assertSame("The Application version 1.2.3\n", $output->fetch());
        $this->assertSame('', $errorOutput->fetch());
    }

    public function getVersionArgs()
    {
        return array(
            array(new StringArgs('-V')),
            array(new StringArgs('--version')),
            array(new StringArgs('command -V')),
            array(new StringArgs('command --version')),
        );
    }

    public function testUseConfiguredStyleSet()
    {
        $styleSet = new StyleSet();
        $styleSet->add(Style::tag('custom'));

        $this->config
            ->setStyleSet($styleSet)

            ->beginCommand('command')
                ->setHandler(new CallbackHandler(function (Args $args, IO $io) {
                    PHPUnit_Framework_Assert::assertSame('text', $io->removeFormat('<custom>text</custom>'));

                    return 123;
                }))
            ->end()
        ;

        $application = new ConsoleApplication($this->config);
        $args = new StringArgs('command');
        $input = new StringInputStream();
        $output = new BufferedOutputStream();
        $errorOutput = new BufferedOutputStream();

        $status = $application->run($args, $input, $output, $errorOutput);

        $this->assertSame(123, $status);
    }
}

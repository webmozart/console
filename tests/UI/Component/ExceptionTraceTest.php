<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\UI\Component;

use PHPUnit_Framework_TestCase;
use RuntimeException;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\UI\Component\ExceptionTrace;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ExceptionTraceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedIO
     */
    private $io;

    private $previousWd;

    protected function setUp()
    {
        $this->io = new BufferedIO();
        $this->previousWd = getcwd();

        // Switch to root directory to fix the output of the relative file paths
        chdir(__DIR__.'/../../..');
    }

    protected function tearDown()
    {
        chdir($this->previousWd);
    }

    public function testRenderNormal()
    {
        $this->io->setVerbosity(IO::NORMAL);

        $exception = NoSuchCommandException::forCommandName('foobar');
        $trace = new ExceptionTrace($exception);
        $trace->render($this->io);

        $expected = 'fatal: The command "foobar" does not exist.'.PHP_EOL;

        $this->assertSame($expected, $this->io->fetchErrors());
    }

    public function testRenderWithoutCauseIfVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $cause = new RuntimeException('The message of the cause.');
        $exception = NoSuchCommandException::forCommandName('foobar', 0, $cause);
        $trace = new ExceptionTrace($exception);
        $trace->render($this->io);

        // Prevent trimming of trailing spaces in the box
        $box = '                                                          '."\n".
               '  [Webmozart\Console\Api\Command\NoSuchCommandException]  '."\n".
               '  The command "foobar" does not exist.                    '."\n".
               '                                                          ';

        $expected = <<<EOF


$box


Exception trace:
  ()
    src/Api/Command/NoSuchCommandException.php:??
  Webmozart\Console\Api\Command\NoSuchCommandException::forCommandName()
    tests/UI/Component/ExceptionTraceTest.php
EOF;

        $actual = $this->io->fetchErrors();

        // Normalize line numbers across PHP and HHVM
        $actual = preg_replace('~(NoSuchCommandException.php:)\d+~', '$1??', $actual);

        // Normalize slashes across OS
        $expected = str_replace(array("\n", '/'), array(PHP_EOL, DIRECTORY_SEPARATOR), $expected);

        // Use to debug issues invisible to the diff output:
        //echo PHP_EOL.unpack('H*', substr($actual, 0, strlen($expected)))[1].PHP_EOL;
        //echo PHP_EOL.unpack('H*', $expected)[1].PHP_EOL;

        $this->assertStringStartsWith($expected, $actual);
        $this->assertNotContains('The message of the cause.', $actual);
        $this->assertStringEndsWith(PHP_EOL.PHP_EOL, $actual);
    }

    public function testRenderWithCauseIfVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $cause = new RuntimeException('The message of the cause.');
        $exception = NoSuchCommandException::forCommandName('foobar', 0, $cause);
        $trace = new ExceptionTrace($exception);
        $trace->render($this->io);

        // Prevent trimming of trailing spaces in the box
        $box1 = '                                                          '."\n".
                '  [Webmozart\Console\Api\Command\NoSuchCommandException]  '."\n".
                '  The command "foobar" does not exist.                    '."\n".
                '                                                          ';

        $expected1 = <<<EOF


$box1


Exception trace:
  ()
    src/Api/Command/NoSuchCommandException.php:??
  Webmozart\Console\Api\Command\NoSuchCommandException::forCommandName()
    tests/UI/Component/ExceptionTraceTest.php
EOF;

        $box2 = '                             '."\n".
                '  [RuntimeException]         '."\n".
                '  The message of the cause.  '."\n".
                '                             ';

        $expected2 = <<<EOF


Caused by:


$box2


Exception trace:
  ()
    tests/UI/Component/ExceptionTraceTest.php
EOF;

        $actual = $this->io->fetchErrors();

        // Normalize line numbers across PHP and HHVM
        $actual = preg_replace('~(NoSuchCommandException.php:)\d+~', '$1??', $actual);

        // Normalize slashes across OS
        $expected1 = str_replace(array("\n", '/'), array(PHP_EOL, DIRECTORY_SEPARATOR), $expected1);
        $expected2 = str_replace(array("\n", '/'), array(PHP_EOL, DIRECTORY_SEPARATOR), $expected2);

        $this->assertStringStartsWith($expected1, $actual);
        $this->assertContains($expected2, $actual);
        $this->assertStringEndsWith(PHP_EOL.PHP_EOL, $actual);
    }
}

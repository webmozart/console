<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Rendering\Exception;

use PHPUnit_Framework_TestCase;
use RuntimeException;
use Webmozart\Console\Api\Command\NoSuchCommandException;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\Rendering\Exception\ExceptionTrace;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ExceptionTraceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedIO
     */
    private $io;

    /**
     * @var Canvas
     */
    private $canvas;

    private $previousWd;

    protected function setUp()
    {
        $this->io = new BufferedIO();
        $this->canvas = new Canvas($this->io, new Dimensions(80, 20));
        $this->canvas->setFlushOnWrite(true);
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
        $trace->render($this->canvas);

        $expected = <<<EOF
fatal: The command "foobar" does not exist.

EOF;

        $this->assertSame($expected, $this->io->fetchErrors());
    }

    public function testRenderWithoutCauseIfVerbose()
    {
        $this->io->setVerbosity(IO::VERBOSE);

        $cause = new RuntimeException('The message of the cause.');
        $exception = NoSuchCommandException::forCommandName('foobar', 0, $cause);
        $trace = new ExceptionTrace($exception);
        $trace->render($this->canvas);

        // Prevent trimming of trailing spaces in the box
        $box = '                                                          '.PHP_EOL.
               '  [Webmozart\Console\Api\Command\NoSuchCommandException]  '.PHP_EOL.
               '  The command "foobar" does not exist.                    '.PHP_EOL.
               '                                                          ';

        $expected = <<<EOF


$box


Exception trace:
  ()
    src/Api/Command/NoSuchCommandException.php:36
  Webmozart\Console\Api\Command\NoSuchCommandException::forCommandName()
    tests/Rendering/Exception/ExceptionTraceTest.php
EOF;

        $this->assertStringStartsWith($expected, $this->io->fetchErrors());
        $this->assertNotContains('The message of the cause.', $this->io->fetchErrors());
        $this->assertStringEndsWith("\n\n", $this->io->fetchErrors());
    }

    public function testRenderWithCauseIfVeryVerbose()
    {
        $this->io->setVerbosity(IO::VERY_VERBOSE);

        $cause = new RuntimeException('The message of the cause.');
        $exception = NoSuchCommandException::forCommandName('foobar', 0, $cause);
        $trace = new ExceptionTrace($exception);
        $trace->render($this->canvas);

        // Prevent trimming of trailing spaces in the box
        $box1 = '                                                          '.PHP_EOL.
                '  [Webmozart\Console\Api\Command\NoSuchCommandException]  '.PHP_EOL.
                '  The command "foobar" does not exist.                    '.PHP_EOL.
                '                                                          ';

        $expected1 = <<<EOF


$box1


Exception trace:
  ()
    src/Api/Command/NoSuchCommandException.php:36
  Webmozart\Console\Api\Command\NoSuchCommandException::forCommandName()
    tests/Rendering/Exception/ExceptionTraceTest.php
EOF;

        $box2 = '                             '.PHP_EOL.
                '  [RuntimeException]         '.PHP_EOL.
                '  The message of the cause.  '.PHP_EOL.
                '                             ';

        $expected2 = <<<EOF


Caused by:


$box2


Exception trace:
  ()
    tests/Rendering/Exception/ExceptionTraceTest.php
EOF;

        $this->assertStringStartsWith($expected1, $this->io->fetchErrors());
        $this->assertContains($expected2, $this->io->fetchErrors());
        $this->assertStringEndsWith("\n\n", $this->io->fetchErrors());
    }

}

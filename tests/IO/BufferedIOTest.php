<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\BufferedIO;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BufferedIOTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $io = new BufferedIO('Lorem ipsum');

        $this->assertSame('Lorem ipsum', $io->readLine());
    }

    public function testSetInput()
    {
        $io = new BufferedIO();
        $io->setInput('Lorem ipsum');

        $this->assertSame('Lorem ipsum', $io->readLine());
    }

    public function testAppendInput()
    {
        $io = new BufferedIO();
        $io->setInput('Lorem ipsum');

        $this->assertSame('Lorem', $io->read(5));

        $io->appendInput(' dolor');

        $this->assertSame(' ipsum dolor', $io->readLine());
    }

    public function testClearInput()
    {
        $io = new BufferedIO();
        $io->setInput('Lorem ipsum');

        $this->assertSame('Lorem', $io->read(5));

        $io->clearInput();

        $this->assertNull($io->readLine());
    }

    public function testFetchOutput()
    {
        $io = new BufferedIO();
        $io->write('Lorem ipsum');

        $this->assertSame('Lorem ipsum', $io->fetchOutput());
    }

    public function testClearOutput()
    {
        $io = new BufferedIO();
        $io->write('Lorem');
        $io->clearOutput();
        $io->write('ipsum');

        $this->assertSame('ipsum', $io->fetchOutput());
    }

    public function testFetchErrors()
    {
        $io = new BufferedIO();
        $io->error('Lorem ipsum');

        $this->assertSame('Lorem ipsum', $io->fetchErrors());
    }

    public function testClearErrors()
    {
        $io = new BufferedIO();
        $io->error('Lorem');
        $io->clearErrors();
        $io->error('ipsum');

        $this->assertSame('ipsum', $io->fetchErrors());
    }
}

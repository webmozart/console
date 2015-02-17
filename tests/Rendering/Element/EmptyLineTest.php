<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Rendering\Element;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\Rendering\Element\EmptyLine;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EmptyLineTest extends PHPUnit_Framework_TestCase
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
        $line = new EmptyLine();
        $line->render($this->canvas);

        $this->assertSame("\n", $this->io->fetchOutput());
    }

    public function testRenderIgnoresIndentation()
    {
        $line = new EmptyLine();
        $line->render($this->canvas, 10);

        $this->assertSame("\n", $this->io->fetchOutput());
    }

}

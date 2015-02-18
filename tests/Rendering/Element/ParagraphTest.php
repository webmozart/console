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
use Webmozart\Console\Adapter\OutputInterfaceAdapter;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Dimensions;
use Webmozart\Console\Rendering\Element\Paragraph;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ParagraphTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt';

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
        $para = new Paragraph(self::LOREM_IPSUM);
        $para->render($this->canvas);

        $expected = <<<EOF
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithIndentation()
    {
        $para = new Paragraph(self::LOREM_IPSUM);
        $para->render($this->canvas, 6);

        $expected = <<<EOF
      Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
      eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }
}

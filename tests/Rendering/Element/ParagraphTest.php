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
use Webmozart\Console\Api\Output\Dimensions;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Rendering\Element\Paragraph;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ParagraphTest extends PHPUnit_Framework_TestCase
{
    const LOREM_IPSUM = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt';

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
        $this->buffer = new BufferedOutput();
        $this->output = new OutputInterfaceAdapter($this->buffer, new Dimensions(80, 20));
    }

    public function testRender()
    {
        $para = new Paragraph(self::LOREM_IPSUM);
        $para->render($this->output);

        $expected = <<<EOF
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
tempor invidunt

EOF;

        $this->assertSame($expected, $this->buffer->fetch());
    }

    public function testRenderWithIndentation()
    {
        $para = new Paragraph(self::LOREM_IPSUM);
        $para->render($this->output, 6);

        $expected = <<<EOF
      Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
      eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->buffer->fetch());
    }
}

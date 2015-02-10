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
use Webmozart\Console\Rendering\Alignment\LabelAlignment;
use Webmozart\Console\Rendering\Element\LabeledParagraph;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class LabeledParagraphTest extends PHPUnit_Framework_TestCase
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
        $para = new LabeledParagraph('Label', 'Text');
        $para->render($this->output);

        $this->assertSame("Label  Text\n", $this->buffer->fetch());
    }

    public function testRenderWithTrailingNewline()
    {
        $para = new LabeledParagraph('Label', "Text\n");
        $para->render($this->output);

        $this->assertSame("Label  Text\n", $this->buffer->fetch());
    }

    public function testRenderWithIndentation()
    {
        $para = new LabeledParagraph('Label', 'Text');
        $para->render($this->output, 4);

        $this->assertSame("    Label  Text\n", $this->buffer->fetch());
    }

    public function testRenderWithLabelDistance()
    {
        $para = new LabeledParagraph('Label', 'Text', 1);
        $para->render($this->output);

        $this->assertSame("Label Text\n", $this->buffer->fetch());
    }

    public function testRenderWithoutText()
    {
        $para = new LabeledParagraph('Label', '');
        $para->render($this->output);

        $this->assertSame("Label\n", $this->buffer->fetch());
    }

    public function testRenderWithAlignment()
    {
        $alignment = new LabelAlignment();
        $alignment->setTextOffset(10);

        $para = new LabeledParagraph('Label', 'Text');
        $para->setAlignment($alignment);
        $para->render($this->output);

        $this->assertSame("Label     Text\n", $this->buffer->fetch());
    }

    public function testRenderWithAlignmentIgnoresIfTextOffsetToSmall()
    {
        $alignment = new LabelAlignment();
        $alignment->setTextOffset(5);

        $para = new LabeledParagraph('Label', 'Text');
        $para->setAlignment($alignment);
        $para->render($this->output);

        $this->assertSame("Label  Text\n", $this->buffer->fetch());
    }

    public function testRenderWrapsText()
    {
        $para = new LabeledParagraph('Label', self::LOREM_IPSUM);
        $para->render($this->output);

        $expected = <<<EOF
Label  Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
       eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->buffer->fetch());
    }

    public function testRenderWithIndentationWrapsText()
    {
        $para = new LabeledParagraph('Label', self::LOREM_IPSUM);
        $para->render($this->output, 4);

        $expected = <<<EOF
    Label  Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
           nonumy eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->buffer->fetch());
    }

    public function testRenderWithLabelDistanceWrapsText()
    {
        $para = new LabeledParagraph('Label', self::LOREM_IPSUM, 6);
        $para->render($this->output);

        $expected = <<<EOF
Label      Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
           nonumy eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->buffer->fetch());
    }
}

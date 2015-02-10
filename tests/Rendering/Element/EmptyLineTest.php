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
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Rendering\Element\EmptyLine;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EmptyLineTest extends PHPUnit_Framework_TestCase
{
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
        $this->output = new OutputInterfaceAdapter($this->buffer);
    }

    public function testRender()
    {
        $line = new EmptyLine();
        $line->render($this->output);

        $this->assertSame("\n", $this->buffer->fetch());
    }

    public function testRenderIgnoresIndentation()
    {
        $line = new EmptyLine();
        $line->render($this->output, 10);

        $this->assertSame("\n", $this->buffer->fetch());
    }

}

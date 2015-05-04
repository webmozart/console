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
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\UI\Component\Paragraph;

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

    protected function setUp()
    {
        $this->io = new BufferedIO();
    }

    public function testRender()
    {
        $para = new Paragraph(self::LOREM_IPSUM);
        $para->render($this->io);

        $expected = <<<EOF
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithIndentation()
    {
        $para = new Paragraph(self::LOREM_IPSUM);
        $para->render($this->io, 6);

        $expected = <<<EOF
      Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
      eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testSkipIndentationForEmptyLines()
    {
        $para = new Paragraph(self::LOREM_IPSUM."\n\n".self::LOREM_IPSUM);
        $para->render($this->io, 6);

        $expected = <<<EOF
      Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
      eirmod tempor invidunt

      Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
      eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }
}

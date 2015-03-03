<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\UI\Layout;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\UI\Component\EmptyLine;
use Webmozart\Console\UI\Component\LabeledParagraph;
use Webmozart\Console\UI\Component\Paragraph;
use Webmozart\Console\UI\Layout\BlockLayout;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class BlockLayoutTest extends PHPUnit_Framework_TestCase
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
        $layout = new BlockLayout();

        $layout
            ->add(new Paragraph('HEADING 1'))
            ->add(new Paragraph(self::LOREM_IPSUM))
            ->add(new EmptyLine())
            ->add(new LabeledParagraph('Not Aligned', self::LOREM_IPSUM, 1, false))
            ->add(new EmptyLine())
            ->add(new Paragraph('HEADING 2'))
            ->beginBlock()
                ->add(new LabeledParagraph('Label 1', self::LOREM_IPSUM))
                ->add(new LabeledParagraph('Label 2', self::LOREM_IPSUM))
            ->endBlock()
            ->add(new Paragraph('HEADING 3'))
            ->beginBlock()
                ->add(new LabeledParagraph('Longer Label', self::LOREM_IPSUM))
            ->endBlock()
        ;

        $layout->render($this->io);

        $expected = <<<EOF
HEADING 1
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
tempor invidunt

Not Aligned Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
            nonumy eirmod tempor invidunt

HEADING 2
  Label 1       Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                diam nonumy eirmod tempor invidunt
  Label 2       Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                diam nonumy eirmod tempor invidunt
HEADING 3
  Longer Label  Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                diam nonumy eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithIndentation()
    {
        $layout = new BlockLayout();

        $layout
            ->add(new Paragraph('HEADING 1'))
            ->add(new Paragraph(self::LOREM_IPSUM))
            ->add(new EmptyLine())
            ->add(new LabeledParagraph('Not Aligned', self::LOREM_IPSUM, 1, false))
            ->add(new EmptyLine())
            ->add(new Paragraph('HEADING 2'))
            ->beginBlock()
                ->add(new LabeledParagraph('Label 1', self::LOREM_IPSUM))
                ->add(new LabeledParagraph('Label 2', self::LOREM_IPSUM))
            ->endBlock()
            ->add(new Paragraph('HEADING 3'))
            ->beginBlock()
                ->add(new LabeledParagraph('Longer Label', self::LOREM_IPSUM))
            ->endBlock()
        ;

        $layout->render($this->io, 4);

        $expected = <<<EOF
    HEADING 1
    Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
    eirmod tempor invidunt

    Not Aligned Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed
                diam nonumy eirmod tempor invidunt

    HEADING 2
      Label 1       Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                    sed diam nonumy eirmod tempor invidunt
      Label 2       Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                    sed diam nonumy eirmod tempor invidunt
    HEADING 3
      Longer Label  Lorem ipsum dolor sit amet, consetetur sadipscing elitr,
                    sed diam nonumy eirmod tempor invidunt

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }
}

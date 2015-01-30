<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Helper;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Console\Helper\WrappedGrid;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class WrappedGridTest extends PHPUnit_Framework_TestCase
{
    public function testAlignCellsNoWrapping()
    {
        // Must be > length of longest cell * 4 (min number of columns)
        // Otherwise the cells will wrap
        $grid = new WrappedGrid(30);
        $grid->addCell('foo');
        $grid->addCell('bar');
        $grid->addCell('hello');
        $grid->addCell('some');
        $grid->addCell('more');
        $grid->addCell('baz');
        $grid->addCell('boom');
        $grid->addCell('stuff');
        $grid->addCell('yes');
        $grid->addCell('no');
        $grid->addCell('wtf');

        $expected = "foo  bar   hello some more baz\n".
                    "boom stuff yes   no   wtf     \n";

        $output = new BufferedOutput();
        $grid->render($output);

        $this->assertSame($expected, $output->fetch());
    }

    public function testAlignCellsNoWrappingIgnoresHighlightTags()
    {
        // Must be > length of longest cell * 4 (min number of columns)
        // Otherwise the cells will wrap
        $grid = new WrappedGrid(30);
        $grid->addCell('<comment>foo</comment>');
        $grid->addCell('bar');
        $grid->addCell('hello');
        $grid->addCell('some');
        $grid->addCell('more');
        $grid->addCell('baz');
        $grid->addCell('boom');
        $grid->addCell('stuff');
        $grid->addCell('yes');
        $grid->addCell('no');
        $grid->addCell('wtf');

        $expected = "foo  bar   hello some more baz\n".
                    "boom stuff yes   no   wtf     \n";

        $output = new BufferedOutput();
        $grid->render($output);

        $this->assertSame($expected, $output->fetch());
    }

    public function testAlignCellsNoWrappingCustomSeparator()
    {
        // Must be > length of longest cell * 4 (min number of columns)
        // Otherwise the cells will wrap
        $grid = new WrappedGrid(30);
        $grid->setHorizontalSeparator(' | ');
        $grid->addCell('foo');
        $grid->addCell('bar');
        $grid->addCell('hello');
        $grid->addCell('some');
        $grid->addCell('more');
        $grid->addCell('baz');
        $grid->addCell('boom');
        $grid->addCell('stuff');
        $grid->addCell('yes');
        $grid->addCell('no');
        $grid->addCell('wtf');

        $expected = "foo  | bar | hello | some \n".
                    "more | baz | boom  | stuff\n".
                    "yes  | no  | wtf   |      \n";

        $output = new BufferedOutput();
        $grid->render($output);

        $this->assertSame($expected, $output->fetch());
    }

    public function testWrapCellsIfTooLong()
    {
        $grid = new WrappedGrid(60);
        $grid->addCell('Lorem ipsum dolor');
        $grid->addCell('sit amet');
        $grid->addCell('consetetur sadipscing');
        $grid->addCell('elitr, sed diam');
        $grid->addCell('nonumy');
        $grid->addCell('eirmod tempor');
        $grid->addCell('invidunt');
        $grid->addCell('ut');
        $grid->addCell('labore');
        $grid->addCell('et dolore magna');

        $expected = "Lorem ipsum    sit amet       consetetur     elitr, sed    \n".
                    "dolor                         sadipscing     diam          \n".
                    "nonumy         eirmod tempor  invidunt       ut            \n".
                    "labore         et dolore                                   \n".
                    "               magna                                       \n";

        $output = new BufferedOutput();
        $grid->render($output);

        $this->assertSame($expected, $output->fetch());
    }

    public function testStretchColumnsIfWrappedContentTooLong()
    {
        // longest text is 10 chars, which is longer than the usual maximum
        // text length (screen width / 4)
        // the third column needs to be stretched over the maximum width
        $grid = new WrappedGrid(30);
        $grid->addCell('Lorem ipsum dolor');
        $grid->addCell('sit amet');
        $grid->addCell('consetetur sadipscing');
        $grid->addCell('elitr, sed diam');
        $grid->addCell('nonumy');
        $grid->addCell('eirmod tempor');
        $grid->addCell('invidunt');
        $grid->addCell('ut');
        $grid->addCell('labore');
        $grid->addCell('et dolore magna');

        $expected = "Lorem    sit    consetetur\n".
                    "ipsum    amet   sadipscing\n".
                    "dolor                     \n".
                    "elitr,   nonumy eirmod    \n".
                    "sed diam        tempor    \n".
                    "invidunt ut     labore    \n".
                    "et                        \n".
                    "dolore                    \n".
                    "magna                     \n";

        $output = new BufferedOutput();
        $grid->render($output);

        $this->assertSame($expected, $output->fetch());
    }
}

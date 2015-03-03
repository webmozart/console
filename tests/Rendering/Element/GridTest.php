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
use Webmozart\Console\IO\BufferedIO;
use Webmozart\Console\Rendering\Element\Alignment;
use Webmozart\Console\Rendering\Element\Grid;
use Webmozart\Console\Rendering\Element\GridStyle;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GridTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var BufferedIO
     */
    private $io;

    protected function setUp()
    {
        $this->io = new BufferedIO();
    }

    public function testRenderAsciiBorder()
    {
        $grid = new Grid(GridStyle::asciiBorder());
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+------------------+-----------------+---------------------+-----------------+
| 99921-58-10-7    | Divine Comedy   | Dante Alighieri     | 9971-5-0210-0   |
+------------------+-----------------+---------------------+-----------------+
| A Tale of Two    | Charles Dickens | 960-425-059-0       | The Lord of the |
| Cities           |                 |                     | Rings           |
+------------------+-----------------+---------------------+-----------------+
| J. R. R. Tolkien | 80-902734-1-6   | And Then There Were | Agatha Christie |
|                  |                 | None                |                 |
+------------------+-----------------+---------------------+-----------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderSolidBorder()
    {
        $grid = new Grid(GridStyle::solidBorder());
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
┌──────────────────┬─────────────────┬─────────────────────┬─────────────────┐
│ 99921-58-10-7    │ Divine Comedy   │ Dante Alighieri     │ 9971-5-0210-0   │
├──────────────────┼─────────────────┼─────────────────────┼─────────────────┤
│ A Tale of Two    │ Charles Dickens │ 960-425-059-0       │ The Lord of the │
│ Cities           │                 │                     │ Rings           │
├──────────────────┼─────────────────┼─────────────────────┼─────────────────┤
│ J. R. R. Tolkien │ 80-902734-1-6   │ And Then There Were │ Agatha Christie │
│                  │                 │ None                │                 │
└──────────────────┴─────────────────┴─────────────────────┴─────────────────┘

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderNoBorder()
    {
        $grid = new Grid(GridStyle::borderless());
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
99921-58-10-7   Divine Comedy   Dante Alighieri 9971-5-0210-0 A Tale of Two
                                                              Cities
Charles Dickens 960-425-059-0   The Lord of the J. R. R.      80-902734-1-6
                                Rings           Tolkien
And Then There  Agatha Christie
Were None

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testSetMinNbColumns()
    {
        $grid = new Grid(GridStyle::asciiBorder());
        $grid->setMinNbColumns(5);
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+-----------------+-------------+-----------------+---------------+------------+
| 99921-58-10-7   | Divine      | Dante Alighieri | 9971-5-0210-0 | A Tale of  |
|                 | Comedy      |                 |               | Two Cities |
+-----------------+-------------+-----------------+---------------+------------+
| Charles Dickens | 960-425-059 | The Lord of the | J. R. R.      | 80-902734- |
|                 | -0          | Rings           | Tolkien       | 1-6        |
+-----------------+-------------+-----------------+---------------+------------+
| And Then There  | Agatha      |                 |               |            |
| Were None       | Christie    |                 |               |            |
+-----------------+-------------+-----------------+---------------+------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testSetMaxNbColumns()
    {
        $grid = new Grid(GridStyle::asciiBorder());
        $grid->setMaxNbColumns(3);
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+---------------+--------------------------+------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
+---------------+--------------------------+------------------+
| 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
+---------------+--------------------------+------------------+
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
+---------------+--------------------------+------------------+
| 80-902734-1-6 | And Then There Were None | Agatha Christie  |
+---------------+--------------------------+------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderFormattedCells()
    {
        $grid = new Grid(GridStyle::asciiBorder());
        $grid->addCells(array(
            '<b>99921-58-10-7</b>', 'Divine Comedy', 'Dante Alighieri',
            '<b>9971-5-0210-0</b>', 'A Tale of Two Cities', 'Charles Dickens',
            '<b>960-425-059-0</b>', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '<b>80-902734-1-6</b>', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+------------------+-----------------+---------------------+-----------------+
| 99921-58-10-7    | Divine Comedy   | Dante Alighieri     | 9971-5-0210-0   |
+------------------+-----------------+---------------------+-----------------+
| A Tale of Two    | Charles Dickens | 960-425-059-0       | The Lord of the |
| Cities           |                 |                     | Rings           |
+------------------+-----------------+---------------------+-----------------+
| J. R. R. Tolkien | 80-902734-1-6   | And Then There Were | Agatha Christie |
|                  |                 | None                |                 |
+------------------+-----------------+---------------------+-----------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithIndentation()
    {
        $grid = new Grid(GridStyle::asciiBorder());
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io, 4);

        $expected = <<<EOF
    +---------------+-----------------+---------------------+-----------------+
    | 99921-58-10-7 | Divine Comedy   | Dante Alighieri     | 9971-5-0210-0   |
    +---------------+-----------------+---------------------+-----------------+
    | A Tale of Two | Charles Dickens | 960-425-059-0       | The Lord of the |
    | Cities        |                 |                     | Rings           |
    +---------------+-----------------+---------------------+-----------------+
    | J. R. R.      | 80-902734-1-6   | And Then There Were | Agatha Christie |
    | Tolkien       |                 | None                |                 |
    +---------------+-----------------+---------------------+-----------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderAlignCenter()
    {
        $style = GridStyle::asciiBorder();
        $style->setCellAlignment(Alignment::CENTER);

        $grid = new Grid($style);
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+------------------+-----------------+---------------------+-----------------+
|  99921-58-10-7   |  Divine Comedy  |   Dante Alighieri   |  9971-5-0210-0  |
+------------------+-----------------+---------------------+-----------------+
|  A Tale of Two   | Charles Dickens |    960-425-059-0    | The Lord of the |
|      Cities      |                 |                     |      Rings      |
+------------------+-----------------+---------------------+-----------------+
| J. R. R. Tolkien |  80-902734-1-6  | And Then There Were | Agatha Christie |
|                  |                 |        None         |                 |
+------------------+-----------------+---------------------+-----------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderAlignRight()
    {
        $style = GridStyle::asciiBorder();
        $style->setCellAlignment(Alignment::RIGHT);

        $grid = new Grid($style);
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
            '9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens',
            '960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien',
            '80-902734-1-6', 'And Then There Were None', 'Agatha Christie',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+------------------+-----------------+---------------------+-----------------+
|    99921-58-10-7 |   Divine Comedy |     Dante Alighieri |   9971-5-0210-0 |
+------------------+-----------------+---------------------+-----------------+
|    A Tale of Two | Charles Dickens |       960-425-059-0 | The Lord of the |
|           Cities |                 |                     |           Rings |
+------------------+-----------------+---------------------+-----------------+
| J. R. R. Tolkien |   80-902734-1-6 | And Then There Were | Agatha Christie |
|                  |                 |                None |                 |
+------------------+-----------------+---------------------+-----------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderAllCellsInOneLine()
    {
        $grid = new Grid(GridStyle::asciiBorder());
        $grid->addCells(array(
            '99921-58-10-7', 'Divine Comedy', 'Dante Alighieri',
        ));

        $grid->render($this->io);

        $expected = <<<EOF
+---------------+---------------+-----------------+
| 99921-58-10-7 | Divine Comedy | Dante Alighieri |
+---------------+---------------+-----------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }
}

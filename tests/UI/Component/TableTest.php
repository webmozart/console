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
use Webmozart\Console\UI\Component\Table;
use Webmozart\Console\UI\Style\Alignment;
use Webmozart\Console\UI\Style\TableStyle;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TableTest extends PHPUnit_Framework_TestCase
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
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+--------------------------+------------------+
| ISBN          | Title                    | Author           |
+---------------+--------------------------+------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
| 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
| 80-902734-1-6 | And Then There Were None | Agatha Christie  |
+---------------+--------------------------+------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderEmpty()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->render($this->io);

        $this->assertSame('', $this->io->fetchOutput());
    }

    public function testRenderSolidBorder()
    {
        $table = new Table(TableStyle::solidBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
┌───────────────┬──────────────────────────┬──────────────────┐
│ ISBN          │ Title                    │ Author           │
├───────────────┼──────────────────────────┼──────────────────┤
│ 99921-58-10-7 │ Divine Comedy            │ Dante Alighieri  │
│ 9971-5-0210-0 │ A Tale of Two Cities     │ Charles Dickens  │
│ 960-425-059-0 │ The Lord of the Rings    │ J. R. R. Tolkien │
│ 80-902734-1-6 │ And Then There Were None │ Agatha Christie  │
└───────────────┴──────────────────────────┴──────────────────┘

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderNoBorder()
    {
        $table = new Table(TableStyle::borderless());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
ISBN          Title                    Author
============= ======================== ================
99921-58-10-7 Divine Comedy            Dante Alighieri
9971-5-0210-0 A Tale of Two Cities     Charles Dickens
960-425-059-0 The Lord of the Rings    J. R. R. Tolkien
80-902734-1-6 And Then There Were None Agatha Christie

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderAlignment()
    {
        $style = TableStyle::asciiBorder();
        $style->setColumnAlignment(1, Alignment::CENTER);
        $style->setColumnAlignment(2, Alignment::RIGHT);

        $table = new Table($style);
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+--------------------------+------------------+
| ISBN          |          Title           |           Author |
+---------------+--------------------------+------------------+
| 99921-58-10-7 |      Divine Comedy       |  Dante Alighieri |
| 9971-5-0210-0 |   A Tale of Two Cities   |  Charles Dickens |
| 960-425-059-0 |  The Lord of the Rings   | J. R. R. Tolkien |
| 80-902734-1-6 | And Then There Were None |  Agatha Christie |
+---------------+--------------------------+------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithWordWrapping()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Divine Comedy Divine Comedy Divine Comedy Divine Comedy Divine Comedy ', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens Charles Dickens Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+------------------------------------+-------------------------+
| ISBN          | Title                              | Author                  |
+---------------+------------------------------------+-------------------------+
| 99921-58-10-7 | Divine Comedy Divine Comedy Divine | Dante Alighieri         |
|               | Comedy Divine Comedy Divine Comedy |                         |
| 9971-5-0210-0 | A Tale of Two Cities               | Charles Dickens Charles |
|               |                                    | Dickens Charles Dickens |
| 960-425-059-0 | The Lord of the Rings              | J. R. R. Tolkien        |
| 80-902734-1-6 | And Then There Were None           | Agatha Christie         |
+---------------+------------------------------------+-------------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithWordWrappingUtf8()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Diviné Cömédy Diviné Cömédy Diviné Cömédy Diviné Cömédy Diviné Cömédy ', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens Charles Dickens Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+-----------------------------+-------------------------+
| ISBN          | Title                       | Author                  |
+---------------+-----------------------------+-------------------------+
| 99921-58-10-7 | Diviné Cömédy Diviné Cömédy | Dante Alighieri         |
|               | Diviné Cömédy Diviné Cömédy |                         |
|               | Diviné Cömédy               |                         |
| 9971-5-0210-0 | A Tale of Two Cities        | Charles Dickens Charles |
|               |                             | Dickens Charles Dickens |
| 960-425-059-0 | The Lord of the Rings       | J. R. R. Tolkien        |
| 80-902734-1-6 | And Then There Were None    | Agatha Christie         |
+---------------+-----------------------------+-------------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithMoreWordWrapping()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7 99921-58-10-7 99921-58-10-7 99921-58-10-7', 'Divine Comedy Divine Comedy Divine Comedy Divine Comedy Divine Comedy ', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens Charles Dickens Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+------------------------------------+-------------------------+
| ISBN          | Title                              | Author                  |
+---------------+------------------------------------+-------------------------+
| 99921-58-10-7 | Divine Comedy Divine Comedy Divine | Dante Alighieri         |
| 99921-58-10-7 | Comedy Divine Comedy Divine Comedy |                         |
| 99921-58-10-7 |                                    |                         |
| 99921-58-10-7 |                                    |                         |
| 9971-5-0210-0 | A Tale of Two Cities               | Charles Dickens Charles |
|               |                                    | Dickens Charles Dickens |
| 960-425-059-0 | The Lord of the Rings              | J. R. R. Tolkien        |
| 80-902734-1-6 | And Then There Were None           | Agatha Christie         |
+---------------+------------------------------------+-------------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithWordCuts()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'DivineComedyDivineComedyDivineComedyDivineComedyDivineComedy ', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens Charles Dickens Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+----------------------------------+-------------------------+
| ISBN          | Title                            | Author                  |
+---------------+----------------------------------+-------------------------+
| 99921-58-10-7 | DivineComedyDivineComedyDivineCo | Dante Alighieri         |
|               | medyDivineComedyDivineComedy     |                         |
| 9971-5-0210-0 | A Tale of Two Cities             | Charles Dickens Charles |
|               |                                  | Dickens Charles Dickens |
| 960-425-059-0 | The Lord of the Rings            | J. R. R. Tolkien        |
| 80-902734-1-6 | And Then There Were None         | Agatha Christie         |
+---------------+----------------------------------+-------------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderWithWordWrappingAndIndentation()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('99921-58-10-7', 'Divine Comedy Divine Comedy Divine Comedy Divine Comedy Divine Comedy ', 'Dante Alighieri'),
            array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens Charles Dickens Charles Dickens'),
            array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io, 4);

        $expected = <<<EOF
    +---------------+-----------------------------+-------------------------+
    | ISBN          | Title                       | Author                  |
    +---------------+-----------------------------+-------------------------+
    | 99921-58-10-7 | Divine Comedy Divine Comedy | Dante Alighieri         |
    |               | Divine Comedy Divine Comedy |                         |
    |               | Divine Comedy               |                         |
    | 9971-5-0210-0 | A Tale of Two Cities        | Charles Dickens Charles |
    |               |                             | Dickens Charles Dickens |
    | 960-425-059-0 | The Lord of the Rings       | J. R. R. Tolkien        |
    | 80-902734-1-6 | And Then There Were None    | Agatha Christie         |
    +---------------+-----------------------------+-------------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    public function testRenderFormattedCells()
    {
        $table = new Table(TableStyle::asciiBorder());
        $table->setHeaderRow(array('ISBN', 'Title', 'Author'));
        $table->addRows(array(
            array('<b>99921-58-10-7</b>', 'Divine Comedy', 'Dante Alighieri'),
            array('<b>9971-5-0210-0</b>', 'A Tale of Two Cities', 'Charles Dickens'),
            array('<b>960-425-059-0</b>', 'The Lord of the Rings', 'J. R. R. Tolkien'),
            array('<b>80-902734-1-6</b>', 'And Then There Were None', 'Agatha Christie'),
        ));

        $table->render($this->io);

        $expected = <<<EOF
+---------------+--------------------------+------------------+
| ISBN          | Title                    | Author           |
+---------------+--------------------------+------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
| 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
| 80-902734-1-6 | And Then There Were None | Agatha Christie  |
+---------------+--------------------------+------------------+

EOF;

        $this->assertSame($expected, $this->io->fetchOutput());
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetHeaderRowFailsIfTooManyCells()
    {
        $table = new Table();
        $table->setRow(0, array('a', 'b', 'c'));
        $table->setHeaderRow(array('a', 'b', 'c', 'd'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetHeaderRowFailsIfMissingCells()
    {
        $table = new Table();
        $table->setRow(0, array('a', 'b', 'c'));
        $table->setHeaderRow(array('a', 'b'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetRowFailsIfTooManyCells()
    {
        $table = new Table();
        $table->setHeaderRow(array('a', 'b', 'c'));
        $table->setRow(0, array('a', 'b', 'c', 'd'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testSetRowFailsIfMissingCells()
    {
        $table = new Table();
        $table->setHeaderRow(array('a', 'b', 'c'));
        $table->setRow(0, array('a', 'b'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddRowFailsIfTooManyCells()
    {
        $table = new Table();
        $table->addRow(array('a', 'b', 'c'));
        $table->addRow(array('a', 'b', 'c', 'd'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddRowFailsIfMissingCells()
    {
        $table = new Table();
        $table->addRow(array('a', 'b', 'c'));
        $table->addRow(array('a', 'b'));
    }
}

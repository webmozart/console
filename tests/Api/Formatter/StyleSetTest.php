<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Formatter;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Formatter\Style;
use Webmozart\Console\Api\Formatter\StyleSet;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StyleSetTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $styleSet = new StyleSet(array(
            $style1 = Style::tag('style1')->fgBlue(),
            $style2 = Style::tag('style2')->bgMagenta(),
        ));

        $this->assertSame(array(
            'style1' => $style1,
            'style2' => $style2,
        ), $styleSet->toArray());
    }

    public function testAdd()
    {
        $styleSet = new StyleSet();
        $styleSet->add($style1 = Style::tag('style1')->fgBlue());
        $styleSet->add($style2 = Style::tag('style2')->bgMagenta());

        $this->assertSame(array(
            'style1' => $style1,
            'style2' => $style2,
        ), $styleSet->toArray());
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddFailsIfNoTag()
    {
        $styleSet = new StyleSet();
        $styleSet->add(Style::noTag());
    }

    public function testMerge()
    {
        $styleSet = new StyleSet();
        $styleSet->add($style1 = Style::tag('style1')->fgBlue());
        $styleSet->merge(array(
            $style2 = Style::tag('style2')->bgMagenta(),
            $style3 = Style::tag('style3')->bold(),
        ));

        $this->assertSame(array(
            'style1' => $style1,
            'style2' => $style2,
            'style3' => $style3,
        ), $styleSet->toArray());
    }

    public function testReplace()
    {
        $styleSet = new StyleSet();
        $styleSet->add($style1 = Style::tag('style1')->fgBlue());
        $styleSet->replace(array(
            $style2 = Style::tag('style2')->bgMagenta(),
            $style3 = Style::tag('style3')->bold(),
        ));

        $this->assertSame(array(
            'style2' => $style2,
            'style3' => $style3,
        ), $styleSet->toArray());
    }

    public function testContains()
    {
        $styleSet = new StyleSet();

        $this->assertFalse($styleSet->contains('style'));
        $this->assertFalse($styleSet->contains('foobar'));

        $styleSet->add(Style::tag('style'));

        $this->assertTrue($styleSet->contains('style'));
        $this->assertFalse($styleSet->contains('foobar'));
    }

    public function testGet()
    {
        $styleSet = new StyleSet(array(
            $style1 = Style::tag('style1')->fgBlue(),
            $style2 = Style::tag('style2')->bgMagenta(),
        ));

        $this->assertSame($style1, $styleSet->get('style1'));
        $this->assertSame($style2, $styleSet->get('style2'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foobar
     */
    public function testGetFailsIfNotFound()
    {
        $styleSet = new StyleSet();

        $styleSet->get('foobar');
    }

    public function testClear()
    {
        $styleSet = new StyleSet();
        $styleSet->add(Style::tag('style1'));
        $styleSet->clear();

        $this->assertSame(array(), $styleSet->toArray());
    }

    public function testIsEmpty()
    {
        $styleSet = new StyleSet();

        $this->assertTrue($styleSet->isEmpty());

        $styleSet->add(Style::tag('style'));

        $this->assertFalse($styleSet->isEmpty());
    }
}

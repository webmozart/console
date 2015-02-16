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

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StyleTest extends PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $style = new Style();

        $this->assertNull($style->getTag());
        $this->assertNull($style->getForegroundColor());
        $this->assertNull($style->getBackgroundColor());
        $this->assertFalse($style->isBold());
        $this->assertFalse($style->isUnderlined());
        $this->assertFalse($style->isBlinking());
        $this->assertFalse($style->isConcealed());
        $this->assertFalse($style->isReversed());
    }

    public function testBlack()
    {
        $style = Style::noTag()->fgBlack()->bgBlack();

        $this->assertSame(Style::BLACK, $style->getForegroundColor());
        $this->assertSame(Style::BLACK, $style->getBackgroundColor());
    }

    public function testRed()
    {
        $style = Style::noTag()->fgRed()->bgRed();

        $this->assertSame(Style::RED, $style->getForegroundColor());
        $this->assertSame(Style::RED, $style->getBackgroundColor());
    }

    public function testGreen()
    {
        $style = Style::noTag()->fgGreen()->bgGreen();

        $this->assertSame(Style::GREEN, $style->getForegroundColor());
        $this->assertSame(Style::GREEN, $style->getBackgroundColor());
    }

    public function testYellow()
    {
        $style = Style::noTag()->fgYellow()->bgYellow();

        $this->assertSame(Style::YELLOW, $style->getForegroundColor());
        $this->assertSame(Style::YELLOW, $style->getBackgroundColor());
    }

    public function testBlue()
    {
        $style = Style::noTag()->fgBlue()->bgBlue();

        $this->assertSame(Style::BLUE, $style->getForegroundColor());
        $this->assertSame(Style::BLUE, $style->getBackgroundColor());
    }

    public function testMagenta()
    {
        $style = Style::noTag()->fgMagenta()->bgMagenta();

        $this->assertSame(Style::MAGENTA, $style->getForegroundColor());
        $this->assertSame(Style::MAGENTA, $style->getBackgroundColor());
    }

    public function testCyan()
    {
        $style = Style::noTag()->fgCyan()->bgCyan();

        $this->assertSame(Style::CYAN, $style->getForegroundColor());
        $this->assertSame(Style::CYAN, $style->getBackgroundColor());
    }

    public function testWhite()
    {
        $style = Style::noTag()->fgWhite()->bgWhite();

        $this->assertSame(Style::WHITE, $style->getForegroundColor());
        $this->assertSame(Style::WHITE, $style->getBackgroundColor());
    }

    public function testTag()
    {
        $style = Style::tag('tag');

        $this->assertSame('tag', $style->getTag());
    }

    public function testFg()
    {
        $style = Style::noTag()->fg(Style::MAGENTA);

        $this->assertSame(Style::MAGENTA, $style->getForegroundColor());
    }

    public function testFgDefault()
    {
        $style = Style::noTag()->fg(Style::MAGENTA)->fgDefault();

        $this->assertNull($style->getForegroundColor());
    }

    public function testBg()
    {
        $style = Style::noTag()->bg(Style::MAGENTA);

        $this->assertSame(Style::MAGENTA, $style->getBackgroundColor());
    }

    public function testBgDefault()
    {
        $style = Style::noTag()->bg(Style::MAGENTA)->bgDefault();

        $this->assertNull($style->getBackgroundColor());
    }

    public function testBold()
    {
        $style = Style::noTag()->bold();

        $this->assertTrue($style->isBold());
    }

    public function testNotBold()
    {
        $style = Style::noTag()->bold()->notBold();

        $this->assertFalse($style->isBold());
    }

    public function testUnderlined()
    {
        $style = Style::noTag()->underlined();

        $this->assertTrue($style->isUnderlined());
    }

    public function testNotUnderlined()
    {
        $style = Style::noTag()->underlined()->notUnderlined();

        $this->assertFalse($style->isUnderlined());
    }

    public function testBlinking()
    {
        $style = Style::noTag()->blinking();

        $this->assertTrue($style->isBlinking());
    }

    public function testNotBlinking()
    {
        $style = Style::noTag()->blinking()->notBlinking();

        $this->assertFalse($style->isBlinking());
    }

    public function testReversed()
    {
        $style = Style::noTag()->reversed();

        $this->assertTrue($style->isReversed());
    }

    public function testNotReversed()
    {
        $style = Style::noTag()->reversed()->notReversed();

        $this->assertFalse($style->isReversed());
    }

    public function testConcealed()
    {
        $style = Style::noTag()->concealed();

        $this->assertTrue($style->isConcealed());
    }

    public function testNotConcealed()
    {
        $style = Style::noTag()->concealed()->notConcealed();

        $this->assertFalse($style->isConcealed());
    }
}

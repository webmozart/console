<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Args;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Args\StringArgs;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class StringArgsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getStringsToParse
     */
    public function testCreate($string, array $tokens)
    {
        $args = new StringArgs($string);

        $this->assertSame($tokens, $args->getTokens());
    }

    public function getStringsToParse()
    {
        return array(
            array('', array()),
            array('foo', array('foo')),
            array('  foo  bar  ', array('foo', 'bar')),
            array('"quoted"', array('quoted')),
            array("'quoted'", array('quoted')),
            array("'a\rb\nc\td'", array("a\rb\nc\td")),
            array("'a'\r'b'\n'c'\t'd'", array('a', 'b', 'c', 'd')),
            array('"quoted \'twice\'"', array('quoted \'twice\'')),
            array("'quoted \"twice\"'", array('quoted "twice"')),
            array('"quoted \'three \"times\"\'"', array('quoted \'three "times"\'')),
            array("'quoted \"three 'times'\"'", array('quoted "three \'times\'"')),
            array("\\'escaped\\'", array('\'escaped\'')),
            array('escaped\\ bar', array('escaped\\', 'bar')),
            array('\"escaped\"', array('"escaped"')),
            array("\\'escaped more\\'", array('\'escaped', 'more\'')),
            array('\"escaped more\"', array('"escaped', 'more"')),
            array('-a', array('-a')),
            array('-azc', array('-azc')),
            array('-awithavalue', array('-awithavalue')),
            array('-a"foo bar"', array('-afoo bar')),
            array('-a"foo bar""foo bar"', array('-afoo barfoo bar')),
            array('-a\'foo bar\'', array('-afoo bar')),
            array('-a\'foo bar\'\'foo bar\'', array('-afoo barfoo bar')),
            array('-a\'foo bar\'"foo bar"', array('-afoo barfoo bar')),
            array('--long-option', array('--long-option')),
            array('--long-option=foo', array('--long-option=foo')),
            array('--long-option="foo bar"', array('--long-option=foo bar')),
            array('--long-option="foo bar""another"', array('--long-option=foo baranother')),
            array('--long-option=\'foo bar\'', array('--long-option=foo bar')),
            array("--long-option='foo bar''another'", array('--long-option=foo baranother')),
            array("--long-option='foo bar'\"another\"", array('--long-option=foo baranother')),
            array('foo -a -ffoo --long bar', array('foo', '-a', '-ffoo', '--long', 'bar')),
            array('\\\' \\"', array('\'', '"')),
        );
    }
}

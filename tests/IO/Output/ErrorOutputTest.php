<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO\Output;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\Output\ErrorOutput;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ErrorOutputTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $output = new ErrorOutput();

        $this->assertInstanceOf('Webmozart\Console\IO\Output\ErrorOutput', $output);
    }
}

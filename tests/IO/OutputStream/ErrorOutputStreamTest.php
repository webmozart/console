<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\IO\OutputStream;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\IO\OutputStream\ErrorOutputStream;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ErrorOutputStreamTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $stream = new ErrorOutputStream();

        $this->assertInstanceOf('Webmozart\Console\IO\OutputStream\ErrorOutputStream', $stream);
    }
}

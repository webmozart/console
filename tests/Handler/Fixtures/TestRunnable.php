<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Handler\Fixtures;

use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Api\Runnable;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TestRunnable implements Runnable
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function run(Input $input, Output $output, Output $errorOutput)
    {
        return call_user_func($this->callback, $input, $output, $errorOutput);
    }
}

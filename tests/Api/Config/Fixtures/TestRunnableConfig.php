<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Config\Fixtures;

use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Api\Runnable;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TestRunnableConfig extends CommandConfig implements Runnable
{
    public function run(Input $input, Output $output, Output $errorOutput)
    {
        return 'foo';
    }
}

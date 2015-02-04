<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Input;

use Webmozart\Console\Api\Command\CommandConfig;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class CommandArgument extends InputArgument
{
    public function __construct(CommandConfig $commandDefinition)
    {
        parent::__construct($commandDefinition->getName(), self::REQUIRED);
    }
}

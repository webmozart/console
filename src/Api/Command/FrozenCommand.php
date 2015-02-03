<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Api\Command;

use Webmozart\Console\Assert\Assert;

/**
 * A command that is always frozen.
 *
 * Frozen commands have a name only and cannot otherwise be modified. They are
 * useful for testing mainly.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class FrozenCommand extends Command
{
    /**
     * Creates a frozen command.
     *
     * @param string $name The name of the command.
     */
    public function __construct($name)
    {
        Assert::string($name, 'The name of a frozen command must be a string. Got: %s');

        parent::__construct($name);

        $this->freeze();
    }
}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering;

use Webmozart\Console\Api\IO\IO;

/**
 * An object that can be printed on the I/O.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Renderable
{
    /**
     * Renders the object.
     *
     * @param IO  $io          The I/O.
     * @param int $indentation The number of spaces to indent.
     */
    public function render(IO $io, $indentation = 0);
}

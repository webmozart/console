<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Rendering\Element;

use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Rendering\Renderable;

/**
 * An empty line.
 *
 * Contrary to a {@link Line} with no text, an empty line is never indented.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class EmptyLine implements Renderable
{
    /**
     * Renders the empty line.
     *
     * @param IO  $io          The I/O.
     * @param int $indentation The number of spaces to indent.
     */
    public function render(IO $io, $indentation = 0)
    {
        // Indentation is ignored for empty lines
        $io->write("\n");
    }
}

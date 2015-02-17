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

/**
 * An object that can be rendered on a canvas.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Renderable
{
    /**
     * Renders the object.
     *
     * @param Canvas $canvas      The canvas to render the object on.
     * @param int    $indentation The number of spaces to indent.
     */
    public function render(Canvas $canvas, $indentation = 0);
}

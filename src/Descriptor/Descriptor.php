<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Descriptor;

use Webmozart\Console\Api\Output\Output;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Descriptor
{
    /**
     * @param Output $output
     * @param        $object
     * @param array  $options
     *
     * @return int The status code.
     */
    public function describe(Output $output, $object, array $options = array());
}

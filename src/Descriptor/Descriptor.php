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

use Webmozart\Console\Api\IO\IO;

/**
 * Describes objects.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface Descriptor
{
    /**
     * Describes an object.
     *
     * @param IO     $io      The I/O.
     * @param object $object  The object to describe.
     * @param array  $options Additional options.
     *
     * @return int The status code.
     */
    public function describe(IO $io, $object, array $options = array());
}

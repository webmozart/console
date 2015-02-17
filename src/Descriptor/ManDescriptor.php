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

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * Describes an object by displaying a man page.
 *
 * The path to the man file should be passed in the "manPath" option.
 * Optionally, you can pass the path to the "man" binary in the "manBinary"
 * option.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ManDescriptor implements Descriptor
{

    /**
     * Describes an object by displaying a man page.
     *
     * This method supports the following options:
     *
     *  * "manPath": The path to the man page. This option is required.
     *  * "manBinary": The path to the "less" binary. If not passed, the path
     *    is searched for on the system.
     *
     * @param IO     $io      The I/O.
     * @param object $object  The object to describe.
     * @param array  $options Additional options.
     *
     * @return int The exit code.
     *
     * @throws InvalidArgumentException If the "asciiDocPath" option is missing.
     * @throws RuntimeException If the AsciiDoc file or the "man" binary is not
     *                           found.
     */
    public function describe(IO $io, $object, array $options = array())
    {
    }
}

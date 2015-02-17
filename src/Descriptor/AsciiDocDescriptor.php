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
 * Describes an object using AsciiDoc documentation.
 *
 * The path to the AsciiDoc file should be passed in the "asciiDocPath" option.
 * Optionally, you can pass the path to the "less" binary in the "lessBinary"
 * option.
 *
 * If "less" is found on the system, it is used to display the AsciiDoc file.
 * Otherwise, the file is simply printed on the output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class AsciiDocDescriptor implements Descriptor
{


    /**
     * Describes an object by displaying an AsciiDoc page.
     *
     * This method supports the following options:
     *
     *  * "asciiDocPath": The path to the AsciiDoc file. This option is required.
     *  * "lessBinary": The path to the "less" binary. If not passed, the path
     *    is searched for on the system.
     *
     * @param IO     $io      The I/O.
     * @param object $object  The object to describe.
     * @param array  $options Additional options.
     *
     * @return int The exit code.
     *
     * @throws InvalidArgumentException If the "asciiDocPath" option is missing.
     * @throws RuntimeException If the AsciiDoc file is not found.
     */
    public function describe(IO $io, $object, array $options = array())
    {
    }
}

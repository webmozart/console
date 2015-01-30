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
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ExecutableFinder;
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
class ManDescriptor implements DescriptorInterface
{
    /**
     * @var ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var ProcessLauncher
     */
    private $processLauncher;

    /**
     * Creates a new AsciiDoc descriptor.
     *
     * @param ExecutableFinder $executableFinder The finder used to find the
     *                                           "man" binary.
     * @param ProcessLauncher  $processLauncher  The launcher for executing the
     *                                           "man" binary.
     */
    public function __construct(ExecutableFinder $executableFinder = null, ProcessLauncher $processLauncher = null)
    {
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->processLauncher = $processLauncher ?: new ProcessLauncher();
    }

    /**
     * Describes an object by displaying a man page.
     *
     * This method supports the following options:
     *
     *  * "manPath": The path to the man page. This option is required.
     *  * "manBinary": The path to the "less" binary. If not passed, the path
     *    is searched for on the system.
     *
     * @param OutputInterface $output  The console output.
     * @param object          $object  The object to describe.
     * @param array           $options Additional options.
     *
     * @return int The exit code.
     *
     * @throws InvalidArgumentException If the "asciiDocPath" option is missing.
     * @throws RuntimeException If the AsciiDoc file or the "man" binary is not
     *                           found.
     */
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        if (!isset($options['manPath'])) {
            throw new InvalidArgumentException('The option "manPath" is required.');
        }

        if (!file_exists($options['manPath'])) {
            throw new RuntimeException(sprintf(
                'The file %s does not exist.',
                $options['manPath']
            ));
        }

        if (!isset($options['manBinary'])) {
            $options['manBinary'] = $this->executableFinder->find('man');
        }

        if (!$options['manBinary']) {
            throw new RuntimeException('The "man" binary was not found.');
        }

        return $this->processLauncher->launchProcess(sprintf(
            '%s -l %s',
            $options['manBinary'],
            escapeshellarg($options['manPath'])
        ), false);
    }
}

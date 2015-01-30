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
class AsciiDocDescriptor implements DescriptorInterface
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
     *                                           "less" binary.
     * @param ProcessLauncher  $processLauncher  The launcher for executing the
     *                                           "less" binary.
     */
    public function __construct(ExecutableFinder $executableFinder = null, ProcessLauncher $processLauncher = null)
    {
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->processLauncher = $processLauncher ?: new ProcessLauncher();
    }

    /**
     * Describes an object by displaying an AsciiDoc page.
     *
     * This method supports the following options:
     *
     *  * "asciiDocPath": The path to the AsciiDoc file. This option is required.
     *  * "lessBinary": The path to the "less" binary. If not passed, the path
     *    is searched for on the system.
     *
     * @param OutputInterface $output  The console output.
     * @param object          $object  The object to describe.
     * @param array           $options Additional options.
     *
     * @return int The exit code.
     *
     * @throws InvalidArgumentException If the "asciiDocPath" option is missing.
     * @throws RuntimeException If the AsciiDoc file is not found.
     */
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        if (!isset($options['asciiDocPath'])) {
            throw new InvalidArgumentException('The option "asciiDocPath" is required.');
        }

        if (!file_exists($options['asciiDocPath'])) {
            throw new RuntimeException(sprintf(
                'The file %s does not exist.',
                $options['asciiDocPath']
            ));
        }

        if (!isset($options['lessBinary'])) {
            $options['lessBinary'] = $this->executableFinder->find('less');
        }

        if ($options['lessBinary'] && $this->processLauncher->isSupported()) {
            return $this->processLauncher->launchProcess(sprintf(
                '%s %s',
                $options['lessBinary'],
                escapeshellarg($options['asciiDocPath'])
            ), false);
        }

        $output->write(file_get_contents($options['asciiDocPath']));

        return 0;
    }
}

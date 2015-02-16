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

use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Input\Input;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * Default descriptor for the "help" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultDescriptor extends DelegatingDescriptor
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
     * Creates a new descriptor.
     *
     * @param ExecutableFinder $executableFinder The executable finder used to
     *                                           discover the "man" and "less"
     *                                           executables.
     * @param ProcessLauncher  $processLauncher  The process launcher used to
     *                                           run "man" and "less".
     */
    public function __construct(ExecutableFinder $executableFinder = null, ProcessLauncher $processLauncher = null)
    {
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->processLauncher = $processLauncher ?: new ProcessLauncher();
    }

    /**
     * Describes an object on the console.
     *
     * The method supports the following options:
     *
     *  * "manDir": The directory to the man pages.
     *  * "asciiDocDir": The directory to the AsciiDoc pages.
     *  * "commandPrefix": The prefix to prepend to pages of commands.
     *  * "defaultPage": The name of the default page.
     *
     * If a command is displayed as man page, the file
     * "{$manDir}/{$commandPrefix}{$page}.1" is displayed using man, where
     * "{$page}" is the command's name with spaces replaced by hyphens.
     *
     * If a command is displayed as AsciiDoc page, the file
     * "{$asciiDocDir}/{$commandPrefix}{$page}.txt" is displayed using less,
     * where "{$page}" is the command's name with spaces replaced by hyphens. If
     * less is not found, the page is simply written to the console output.
     *
     * If the passed object is no command, the page passed in the "defaultPage"
     * option will be displayed.
     *
     * @param Output $output  The console output.
     * @param object $object  The object to describe.
     * @param array  $options Additional options.
     *
     * @return int The exit code.
     */
    public function describe(Output $output, $object, array $options = array())
    {
        $options = array_replace(array(
            'manDir' => getcwd().'/docs',
            'asciiDocDir' => getcwd().'/docs',
            'commandPrefix' => '',
            'defaultPage' => 'default',
        ), $options);

        $page = $object instanceof Command
            ? $options['commandPrefix'].str_replace(' ', '-', $object->getName())
            : $options['defaultPage'];

        $options['manBinary'] = $this->executableFinder->find('man');
        $options['manPath'] = $options['manDir'].'/'.$page.'.1';
        $options['asciiDocPath'] = $options['asciiDocDir'].'/'.$page.'.txt';

        return (int) parent::describe($output, $object, $options);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseFormat(Args $args, $object, array $options = array())
    {
        $rawArgs = $args->getRawArgs();

        // Check whether the raw arguments are available
        if (!$rawArgs) {
            return 'text';
        }

        // If "-h" is given, always print the short text usage
        if ($rawArgs->hasToken('-h')) {
            return 'text';
        }

        // Check if any of the options is set
        foreach (array('man', 'ascii-doc', 'xml', 'json', 'text') as $format) {
            if ($rawArgs->hasToken('--'.$format)) {
                return $format;
            }
        }

        // No format option is set, "-h" is not set
        // If a command is given or if "--help" is set, display the manual
        if ($rawArgs->hasToken('--help')) {
            // Return "man" if the binary is available and the man page exists
            // The process launcher must be supported on the system
            if ($options['manBinary'] && file_exists($options['manPath']) && $this->processLauncher->isSupported()) {
                return 'man';
            }

            if (file_exists($options['asciiDocPath'])) {
                return 'ascii-doc';
            }
        }

        // No command, no option -> display command list as text
        return 'text';
    }

    protected function getDefaultFormat()
    {
        return 'text';
    }

    protected function getDescriptor($format)
    {
        // Create descriptors on demand
        switch ($format) {
            case 'man':
                return new ManDescriptor($this->executableFinder, $this->processLauncher);
            case 'ascii-doc':
                return new AsciiDocDescriptor($this->executableFinder, $this->processLauncher);
            case 'xml':
                return new XmlDescriptor();
            case 'json':
                return new JsonDescriptor();
            case 'text':
                return new TextDescriptor();
        }

        return parent::getDescriptor($format);
    }
}

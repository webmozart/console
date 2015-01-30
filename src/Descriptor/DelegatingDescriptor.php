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

use RuntimeException;
use Symfony\Component\Console\Descriptor\DescriptorInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Describes an object using other registered descriptors.
 *
 * You can register descriptors for format strings with the {@link register()}
 * method. When calling {@link describe()}, the format is extracted from the
 * console input passed via the "input" option. The matching descriptor for the
 * extracted format is used to display the object.
 *
 * If no format can be found, the first registered descriptor is used. You can
 * optionally pass a default format to {@link __construct()}.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DelegatingDescriptor implements DescriptorInterface
{
    /**
     * @var DescriptorInterface[]
     */
    private $descriptors = array();

    /**
     * @var string|null
     */
    private $defaultFormat;

    /**
     * Creates a new delegating descriptor.
     *
     * @param string|null $defaultFormat The format to be used by default. If
     *                                   none is passed, the first registered
     *                                   descriptor is used by default.
     */
    public function __construct($defaultFormat = null)
    {
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Describes an object on the console.
     *
     * You can pass descriptors for several formats by calling
     * {@link register()}.
     *
     * This method supports the option "input" where you may pass the console
     * input. The used format will be read from the "format" option of the
     * input. If no format is found, the first registered descriptor is used
     * by default. You can also pass a default format to {@link __construct()}.
     *
     * @param OutputInterface $output  The console output.
     * @param object          $object  The object to describe.
     * @param array           $options Additional options.
     *
     * @return int The exit code.
     *
     * @throws RuntimeException If the format is not supported.
     */
    public function describe(OutputInterface $output, $object, array $options = array())
    {
        $format = isset($options['input'])
            ? $this->parseFormat($options['input'], $object, $options)
            : $this->getDefaultFormat();

        return (int) $this->getDescriptor($format)->describe($output, $object, $options);
    }

    /**
     * Registers a descriptor for a format.
     *
     * @param string              $format     The format.
     * @param DescriptorInterface $descriptor The descriptor.
     */
    public function register($format, DescriptorInterface $descriptor)
    {
        $this->descriptors[$format] = $descriptor;
    }

    /**
     * Returns the described format for the given console input.
     *
     * @param InputInterface $input   The console input.
     * @param object         $object  The described options.
     * @param array          $options Additional options.
     *
     * @return string The format to display.
     */
    protected function parseFormat(InputInterface $input, $object, array $options = array())
    {
        if ($input->hasOption('format')) {
            return $input->getOption('format') ?: $this->getDefaultFormat();
        }

        return $this->getDefaultFormat();
    }

    /**
     * Returns the format to be used when no format is passed.
     *
     * @return string The format to display by default.
     */
    protected function getDefaultFormat()
    {
        if ($this->defaultFormat) {
            return $this->defaultFormat;
        }

        reset($this->descriptors);

        // Use first registered descriptor by default
        return key($this->descriptors);
    }

    /**
     * Returns the descriptor for the given format.
     *
     * @param string $format The format.
     *
     * @return DescriptorInterface The descriptor.
     *
     * @throws RuntimeException If the format is not supported.
     */
    protected function getDescriptor($format)
    {
        if (!isset($this->descriptors[$format])) {
            throw new RuntimeException(sprintf('Unsupported format "%s".', $format));
        }

        return $this->descriptors[$format];
    }
}

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
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Command\NamedCommand;
use Webmozart\Console\Api\Config\OptionCommandConfig;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\Api\IO\Output;
use Webmozart\Console\Rendering\Canvas;
use Webmozart\Console\Rendering\Element\EmptyLine;
use Webmozart\Console\Rendering\Element\LabeledParagraph;
use Webmozart\Console\Rendering\Element\Paragraph;
use Webmozart\Console\Rendering\Layout\BlockLayout;

/**
 * Describes an object as text on the console output.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TextDescriptor implements Descriptor
{
    /**
     * @var \Webmozart\Console\Rendering\Layout\BlockLayout
     */
    private $layout;

    /**
     * Describes an object as text on the console output.
     *
     * @param IO                  $io      The I/O.
     * @param Command|Application $object  The object to describe.
     * @param array               $options Additional options.
     *
     * @return int The exit code.
     */
    public function describe(IO $io, $object, array $options = array())
    {
        $this->layout = new BlockLayout();



        $canvas = new Canvas($io);
        $this->layout->render($canvas);
        $canvas->flush();

        return 0;
    }

    /**
     * Describes an application.
     *
     * @param Application $application The application to describe.
     * @param array       $options     Additional options.
     */
    protected function describeApplication(Application $application, array $options = array())
    {
    }
}

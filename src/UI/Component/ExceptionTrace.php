<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\UI\Component;

use Exception;
use Webmozart\Console\Api\IO\IO;
use Webmozart\Console\UI\Component;

/**
 * Renders the trace of an exception.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ExceptionTrace implements Component
{
    /**
     * @var Exception
     */
    private $exception;

    /**
     * Creates a renderer for the given exception.
     *
     * @param Exception $exception The exception to render.
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Renders the exception trace.
     *
     * @param IO  $io          The I/O.
     * @param int $indentation The number of spaces to indent.
     */
    public function render(IO $io, $indentation = 0)
    {
        if (!$io->isVerbose()) {
            $io->errorLine('fatal: '.$this->exception->getMessage());

            return;
        }

        $exception = $this->exception;

        $this->renderException($io, $exception);

        if ($io->isVeryVerbose()) {
            while ($exception = $exception->getPrevious()) {
                $io->errorLine('Caused by:');

                $this->renderException($io, $exception);
            }
        }
    }

    private function renderException(IO $io, Exception $exception)
    {
        $this->printBox($io, $exception);
        $this->printTrace($io, $exception);
    }

    private function printBox(IO $io, Exception $exception)
    {
        $screenWidth = $io->getTerminalDimensions()->getWidth() - 1;
        $boxWidth = 0;

        $boxLines = array_merge(
            array(sprintf('[%s]', get_class($exception))),
            // TODO replace by implementation that is aware of format codes
            explode("\n", wordwrap($exception->getMessage(), $screenWidth - 4))
        );

        foreach ($boxLines as $line) {
            $boxWidth = max($boxWidth, strlen($line));
        }

        // TODO handle $boxWidth > $screenWidth
        $emptyLine = sprintf('<error>%s</error>', str_repeat(' ', $boxWidth + 4));

        $io->errorLine('');
        $io->errorLine('');
        $io->errorLine($emptyLine);

        foreach ($boxLines as $boxLine) {
            $padding = str_repeat(' ', max(0, $boxWidth - strlen($boxLine)));
            $io->errorLine(sprintf('<error>  %s%s  </error>', $boxLine, $padding));
        }

        $io->errorLine($emptyLine);
        $io->errorLine('');
        $io->errorLine('');
    }

    private function printTrace(IO $io, Exception $exception)
    {
        $traces = $exception->getTrace();
        $cwd = getcwd().'/';
        $cwdLength = strlen($cwd);

        $lastTrace = array(
            'function' => '',
            'args' => array(),
        );

        if (null !== $exception->getFile()) {
            $lastTrace['file'] = $exception->getFile();
        }

        if (null !== $exception->getLine()) {
            $lastTrace['line'] = $exception->getLine();
        }

        array_unshift($traces, $lastTrace);

        $io->errorLine('<b>Exception trace:</b>');

        foreach ($traces as $trace) {
            $namespace = '';
            $class = '';
            $location = 'n/a';

            if (isset($trace['class'])) {
                if (false !== $pos = strrpos($trace['class'], '\\')) {
                    $namespace = substr($trace['class'], 0, $pos + 1);
                    $class = substr($trace['class'], $pos + 1);
                } else {
                    $class = $trace['class'];
                }
            }

            if (isset($trace['file'])) {
                if (0 === strpos($trace['file'], $cwd)) {
                    $location = substr($trace['file'], $cwdLength);
                } else {
                    $location = $trace['file'];
                }
            }

            // class, operator, function
            $signature = $class.(isset($trace['type']) ? $trace['type'] : '').$trace['function'];
            $location .= ':'.(isset($trace['line']) ? $trace['line'] : 'n/a');

            $io->errorRaw(sprintf("  %s%s()\n    %s\n",
                $namespace,
                $io->format('<u>'.$signature.'</u>'),
                $io->format('<em>'.$location.'</em>')
            ));
        }

        $io->errorLine('');
        $io->errorLine('');
    }
}

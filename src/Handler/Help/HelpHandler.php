<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Handler\Help;

use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Api\Application\Application;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Assert\Assert;
use Webmozart\Console\Handler\DelegatingHandler;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * Handler for the "help" command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpHandler extends DelegatingHandler
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
     * @var string
     */
    private $manBinary;

    /**
     * @var string
     */
    private $manDir;

    /**
     * @var string
     */
    private $lessBinary;

    /**
     * @var string
     */
    private $asciiDocDir;

    /**
     * @var string
     */
    private $applicationPage;

    /**
     * @var string
     */
    private $commandPagePrefix;

    /**
     * Creates the handler.
     *
     * @param ExecutableFinder $executableFinder The finder used to find the
     *                                           "less"/"man" binaries.
     * @param ProcessLauncher  $processLauncher  The launcher for executing the
     *                                           "less"/"man" binaries.
     */
    public function __construct(ExecutableFinder $executableFinder = null, ProcessLauncher $processLauncher = null)
    {
        $this->executableFinder = $executableFinder ?: new ExecutableFinder();
        $this->processLauncher = $processLauncher ?: new ProcessLauncher();
        $this->asciiDocDir = getcwd().'/docs/ascii-doc';
        $this->manDir = getcwd().'/docs/man';

        $this->register('ascii-doc', array($this, 'createAsciiDocHandler'));
        $this->register('json', array($this, 'createJsonHandler'));
        $this->register('man', array($this, 'createManHandler'));
        $this->register('text', array($this, 'createTextHandler'));
        $this->register('xml', array($this, 'createXmlHandler'));
        $this->selectHandler(array($this, 'getHandlerToRun'));
    }

    /**
     * Callback for creating the "--ascii-doc" handler.
     *
     * @param Command $command The handled command.
     * @param Args    $args    The console arguments.
     *
     * @return HelpAsciiDocHandler The created handler.
     */
    public function createAsciiDocHandler(Command $command, Args $args)
    {
        $path = $this->getAsciiDocPage($command->getApplication(), $args);

        $handler = new HelpAsciiDocHandler($path, $this->executableFinder, $this->processLauncher);
        $handler->setLessBinary($this->lessBinary);

        return $handler;
    }

    /**
     * Callback for creating the "--json" handler.
     *
     * @return HelpJsonHandler The created handler.
     */
    public function createJsonHandler()
    {
        return new HelpJsonHandler();
    }

    /**
     * Callback for creating the "--man" handler.
     *
     * @param Command $command The handled command.
     * @param Args    $args    The console arguments.
     *
     * @return HelpManHandler The created handler.
     */
    public function createManHandler(Command $command, Args $args)
    {
        $path = $this->getManPage($command->getApplication(), $args);

        $handler = new HelpManHandler($path, $this->executableFinder, $this->processLauncher);
        $handler->setManBinary($this->manBinary);

        return $handler;
    }

    /**
     * Callback for creating the "--text" handler.
     *
     * @return HelpTextHandler The created handler.
     */
    public function createTextHandler()
    {
        return new HelpTextHandler();
    }

    /**
     * Callback for creating the "--xml" handler.
     *
     * @return HelpXmlHandler The created handler.
     */
    public function createXmlHandler()
    {
        return new HelpXmlHandler();
    }

    /**
     * Callback for selecting the handler that should be run.
     *
     * @param Command $command The handled command.
     * @param Args    $args    The console arguments.
     *
     * @return string The name of the handler to run.
     */
    public function getHandlerToRun(Command $command, Args $args)
    {
        $rawArgs = $args->getRawArgs();

        // The raw arguments should always be available, but check anyway
        if (!$rawArgs) {
            return 'text';
        }

        // If "-h" is given, always print the short text usage
        if ($rawArgs->hasToken('-h')) {
            return 'text';
        }

        // Check if any of the options is set
        foreach ($this->getRegisteredNames() as $handlerName) {
            if ($rawArgs->hasToken('--'.$handlerName)) {
                return $handlerName;
            }
        }

        // No format option is set, "-h" is not set
        // If a command is given or if "--help" is set, display the manual
        if ($rawArgs->hasToken('--help')) {
            // Return "man" if the binary is available and the man page exists
            // The process launcher must be supported on the system
            $manPage = $this->getManPage($command->getApplication(), $args);

            if (file_exists($manPage) && $this->processLauncher->isSupported()) {
                if (!$this->manBinary) {
                    $this->manBinary = $this->executableFinder->find('man');
                }

                if ($this->manBinary) {
                    return 'man';
                }
            }

            // Return "ascii-doc" if the AsciiDoc page exists
            $asciiDocPage = $this->getAsciiDocPage($command->getApplication(), $args);

            if (file_exists($asciiDocPage)) {
                return 'ascii-doc';
            }
        }

        // No command, no option -> display command list as text
        return 'text';
    }

    /**
     * Returns the "man" binary used to display the man pages.
     *
     * @return string The "man" binary or `null` if the binary is auto-detected.
     */
    public function getManBinary()
    {
        return $this->manBinary;
    }

    /**
     * Sets the "man" binary used to display the AsciiDoc pages.
     *
     * @param string $manBinary The "man" binary to use.
     */
    public function setManBinary($manBinary)
    {
        if (null !== $manBinary) {
            Assert::string($manBinary, 'The man binary must be a string or null. Got: %s');
            Assert::notEmpty($manBinary, 'The man binary must not be empty.');
        }

        $this->manBinary = $manBinary;
    }

    /**
     * Returns the "less" binary used to display the AsciiDoc pages.
     *
     * @return string The "less" binary or `null` if the binary is auto-detected.
     */
    public function getLessBinary()
    {
        return $this->lessBinary;
    }

    /**
     * Sets the "less" binary used to display the AsciiDoc pages.
     *
     * @param string $lessBinary The "less" binary to use.
     */
    public function setLessBinary($lessBinary)
    {
        if (null !== $lessBinary) {
            Assert::string($lessBinary, 'The less binary must be a string or null. Got: %s');
            Assert::notEmpty($lessBinary, 'The less binary must not be empty.');
        }

        $this->lessBinary = $lessBinary;
    }

    /**
     * Returns the directory containing the man pages.
     *
     * @return string The directory that contains the man pages.
     */
    public function getManDir()
    {
        return $this->manDir;
    }

    /**
     * Sets the directory containing the man pages.
     *
     * @param string $dir The directory that contains the man pages.
     */
    public function setManDir($dir)
    {
        Assert::directory($dir);

        $this->manDir = $dir;
    }

    /**
     * Returns the directory containing the AsciiDoc pages.
     *
     * @return string The directory that contains the AsciiDoc pages.
     */
    public function getAsciiDocDir()
    {
        return $this->asciiDocDir;
    }

    /**
     * Sets the directory containing the AsciiDoc pages.
     *
     * @param string $dir The directory that contains the AsciiDoc pages.
     */
    public function setAsciiDocDir($dir)
    {
        Assert::directory($dir);

        $this->asciiDocDir = $dir;
    }

    /**
     * Returns the name of the file displayed when the application help is
     * shown with less/man.
     *
     * @return string The application page.
     */
    public function getApplicationPage()
    {
        return $this->applicationPage;
    }

    /**
     * Sets the name of the file displayed when the application help is shown
     * with less/man.
     *
     * @param string $page The application page.
     */
    public function setApplicationPage($page)
    {
        if (null !== $page) {
            Assert::string($page, 'The application page must be a string or null. Got: %s');
            Assert::notEmpty($page, 'The application page must not be empty.');
        }

        $this->applicationPage = $page;
    }

    /**
     * Returns the prefix of the files displayed when a command help is shown
     * with less/man.
     *
     * @return string The page prefix.
     */
    public function getCommandPagePrefix()
    {
        return $this->commandPagePrefix;
    }

    /**
     * Sets the prefix of the files displayed when a command help is shown with
     * less/man.
     *
     * @param string $prefix The page prefix.
     */
    public function setCommandPagePrefix($prefix)
    {
        $this->commandPagePrefix = $prefix;
    }

    private function getAsciiDocPage(Application $application, Args $args)
    {
        return $this->asciiDocDir.'/'.$this->getPageName($application, $args).'.txt';
    }

    private function getManPage(Application $application, Args $args)
    {
        return $this->manDir.'/'.$this->getPageName($application, $args).'.1';
    }

    private function getPageName(Application $application, Args $args)
    {
        if ($args->isArgumentSet('command')) {
            $command = $application->getCommand($args->getArgument('command'));

            return $this->commandPagePrefix.$command->getName();
        }

        if ($this->applicationPage) {
            return $this->applicationPage;
        }

        return $application->getConfig()->getName();
    }


}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Webmozart\Console\Application;
use Webmozart\Console\Input\InputDefinition;
use Webmozart\Console\Input\InputOption;

/**
 * A command.
 *
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Command extends \Symfony\Component\Console\Command\Command
{
    const COMMAND_ARG = 'command-name';

    /**
     * @var InputDefinition
     */
    private $localDefinition;

    /**
     * @var string|null
     */
    private $processTitle;

    /**
     * @var string[]
     */
    private $synopsises = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        // Use custom InputDefinition implementation
        $inputDefinition = new InputDefinition();
        $inputDefinition->addArguments($this->getDefinition()->getArguments());
        $inputDefinition->addOptions($this->getDefinition()->getOptions());

        $this->setDefinition($inputDefinition);

        // Remember this input definition later on to get the synopsis without
        // the application options/arguments
        $this->localDefinition = clone $inputDefinition;
    }

    public function getLocalDefinition()
    {
        return $this->localDefinition;
    }

    /**
     * @return null|string
     */
    public function getProcessTitle()
    {
        return $this->processTitle;
    }

    /**
     * @param null|string $processTitle
     *
     * @return $this
     */
    public function setProcessTitle($processTitle)
    {
        parent::setProcessTitle($processTitle);

        $this->processTitle = $processTitle;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null, $valueName = '...')
    {
        $this->getDefinition()->addOption(new InputOption($name, $shortcut, $mode, $description, $default, $valueName));

        return $this;
    }

    /**
     * Returns the synopsises of the command.
     *
     * A synopsis is a short usage example of the command's options and
     * arguments. Synopsises can be added with {@link setSynopsises()} and
     * {@link addSynopsis()}. If no synopsis was added, the one of the
     * command's input definition is returned.
     *
     * @return string[] The synopsises.
     */
    public function getSynopsises()
    {
        return $this->synopsises ?: array($this->localDefinition->getSynopsis());
    }

    /**
     * Sets the synopsises of the command.
     *
     * A synopsis is a short usage example of the command's options and
     * arguments.
     *
     * @param string[] $synopsises The synopsises.
     */
    public function setSynopsises(array $synopsises)
    {
        $this->synopsises = $synopsises;
    }

    /**
     * Adds a synopsis.
     *
     * A synopsis is a short usage example of the command's options and
     * arguments.
     *
     * @param string $synopsis The synopsis to add.
     *
     * @return static Returns this command.
     */
    public function addSynopsis($synopsis)
    {
        $this->synopsises[] = $synopsis;

        return $this;
    }

    /**
     * Sets the application instance for this command.
     *
     * @param \Symfony\Component\Console\Application $application The application.
     *
     * @throws InvalidArgumentException If the application is not an instance
     *                                   of {@link Application}.
     */
    public function setApplication(\Symfony\Component\Console\Application $application = null)
    {
        if ($application !== null && !$application instanceof Application) {
            throw new InvalidArgumentException(sprintf(
                'The application should be an instance of Application or '.
                'null. Got: %s',
                is_object($application) ? get_class($application) : gettype($application)
            ));
        }

        parent::setApplication($application);
    }

    /**
     * Returns the application instance for this command.
     *
     * @return Application An Application instance
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    public function mergeApplicationDefinition($mergeArgs = true)
    {
        // Never merge application arguments
        parent::mergeApplicationDefinition(false);

        $inputDefinition = $this->getDefinition();

        // Add "command-name" argument
        if ($mergeArgs && !$inputDefinition->hasArgument(self::COMMAND_ARG)) {
            $arguments = $inputDefinition->getArguments();
            $inputDefinition->setArguments(array(new InputArgument(self::COMMAND_ARG, InputArgument::REQUIRED)));
            $inputDefinition->addArguments($arguments);
        }
    }
}

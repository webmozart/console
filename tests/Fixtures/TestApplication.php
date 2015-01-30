<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Fixtures;

use Symfony\Component\Console\Input\InputOption;
use Webmozart\Console\Application;
use Webmozart\Console\Input\InputDefinition;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TestApplication extends Application
{
    private $terminalDimensions;

    public function __construct($terminalDimensions = array(null, null))
    {
        parent::__construct('Test Application', '1.0.0', 'test-bin');

        $this->terminalDimensions = $terminalDimensions;
    }

    public function getTerminalDimensions()
    {
        return $this->terminalDimensions;
    }

    protected function getDefaultCommands()
    {
        return array_merge(parent::getDefaultCommands(), array(
            new TestPackCommand(),
            new TestPackageCommand(),
            new TestPackageAddCommand(),
            new TestPackageAddonCommand(),
        ));
    }

    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputOption('help', 'h', InputOption::VALUE_NONE, 'Description'),
            new InputOption('quiet', 'q', InputOption::VALUE_NONE, 'Description'),
            new InputOption('verbose', '', InputOption::VALUE_NONE, 'Description'),
            new InputOption('version', 'V', InputOption::VALUE_NONE, 'Description'),
            new InputOption('ansi', '', InputOption::VALUE_NONE, 'Description'),
            new InputOption('no-ansi', '', InputOption::VALUE_NONE, 'Description'),
            new InputOption('no-interaction', 'n', InputOption::VALUE_NONE, 'Description'),
        ));
    }
}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Process;

use PHPUnit_Framework_TestCase;
use Symfony\Component\Process\PhpExecutableFinder;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * @since  1.0
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ProcessLauncherTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProcessLauncher
     */
    private $launcher;

    /**
     * @var string
     */
    private $php;

    protected function setUp()
    {
        $finder = new PhpExecutableFinder();
        $this->php = escapeshellcmd($finder->find());
        $this->launcher = new ProcessLauncher();

        // Speed up the tests
        $this->launcher->setCheckInterval(0.01);
    }

    public function testLaunchSuccessfully()
    {
        if (!$this->php) {
            $this->markTestSkipped('The "bash" binary is not available.');

            return;
        }

        if (!function_exists('proc_open')) {
            $this->markTestSkipped('The "proc_open" function is not available.');

            return;
        }

        $status = $this->launcher->launchProcess($this->php.' -r %command%', array(
            'command' => 'exit(0);',
        ));

        $this->assertSame(0, $status);
    }

    public function testLaunchWithError()
    {
        if (!$this->php) {
            $this->markTestSkipped('The "bash" binary is not available.');

            return;
        }

        if (!function_exists('proc_open')) {
            $this->markTestSkipped('The "proc_open" function is not available.');

            return;
        }

        $status = $this->launcher->launchProcess($this->php.' -r %command%', array(
            'command' => 'exit(123);',
        ));

        $this->assertSame(123, $status);
    }
}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Descriptor;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\ExecutableFinder;
use Webmozart\Console\Descriptor\AsciiDocDescriptor;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class AsciiDocDescriptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ExecutableFinder
     */
    private $executableFinder;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ProcessLauncher
     */
    private $processLauncher;

    /**
     * @var AsciiDocDescriptor
     */
    private $descriptor;

    protected function setUp()
    {
        $this->executableFinder = $this->getMockBuilder('Symfony\Component\Process\ExecutableFinder')
            ->disableOriginalConstructor()
            ->getMock();
        $this->processLauncher = $this->getMockBuilder('Webmozart\Console\Process\ProcessLauncher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->descriptor = new AsciiDocDescriptor($this->executableFinder, $this->processLauncher);
    }

    public function testDescribe()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $command = sprintf("less-binary '%s'", __DIR__.'/Fixtures/ascii-doc/command1.txt');

        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'asciiDocPath' => __DIR__.'/Fixtures/ascii-doc/command1.txt',
        ));

        $this->assertSame(123, $status);
    }

    public function testDescribePrintsToOutputIfLessNotFound()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue(null));

        $this->processLauncher->expects($this->never())
            ->method('isSupported');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'asciiDocPath' => __DIR__.'/Fixtures/ascii-doc/command1.txt',
        ));

        $this->assertSame("Contents of command1.txt\n", $output->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribePrintsToOutputIfProcessLauncherNotSupported()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('less')
            ->will($this->returnValue('less-binary'));

        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(false));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'asciiDocPath' => __DIR__.'/Fixtures/ascii-doc/command1.txt',
        ));

        $this->assertSame("Contents of command1.txt\n", $output->fetch());
        $this->assertSame(0, $status);
    }

    public function testDescribeWithCustomLessBinary()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->never())
            ->method('find');

        $command = sprintf("my-less '%s'", __DIR__.'/Fixtures/ascii-doc/command1.txt');

        $this->processLauncher->expects($this->once())
            ->method('isSupported')
            ->will($this->returnValue(true));

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'asciiDocPath' => __DIR__.'/Fixtures/ascii-doc/command1.txt',
            'lessBinary' => 'my-less',
        ));

        $this->assertSame(123, $status);
    }

    public function testDescribePrintsToOutputIfCustomLessBinaryNotFound()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->never())
            ->method('isSupported');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'asciiDocPath' => __DIR__.'/Fixtures/ascii-doc/command1.txt',
            'lessBinary' => false,
        ));

        $this->assertSame("Contents of command1.txt\n", $output->fetch());
        $this->assertSame(0, $status);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testDescribeFailsIfFileNotPassed()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->never())
            ->method('isSupported');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $this->descriptor->describe($output, new \stdClass());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDescribeFailsIfFileNotFound()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->never())
            ->method('isSupported');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $this->descriptor->describe($output, new \stdClass(), array(
            'asciiDocPath' => __DIR__.'/foobar.txt',
        ));
    }
}

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
use Webmozart\Console\Descriptor\ManDescriptor;
use Webmozart\Console\Process\ProcessLauncher;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ManDescriptorTest extends PHPUnit_Framework_TestCase
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
     * @var ManDescriptor
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

        $this->descriptor = new ManDescriptor($this->executableFinder, $this->processLauncher);
    }

    public function testDescribe()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue('man-binary'));

        $command = sprintf("man-binary -l '%s'", __DIR__.'/Fixtures/man/package.1');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'manPath' => __DIR__.'/Fixtures/man/package.1',
        ));

        $this->assertSame(123, $status);
    }

    public function testDescribeWithCustomManBinary()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->never())
            ->method('find');

        $command = sprintf("my-man -l '%s'", __DIR__.'/Fixtures/man/package.1');

        $this->processLauncher->expects($this->once())
            ->method('launchProcess')
            ->with($command, false)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($output, new \stdClass(), array(
            'manPath' => __DIR__.'/Fixtures/man/package.1',
            'manBinary' => 'my-man',
        ));

        $this->assertSame(123, $status);
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
            ->method('launchProcess');

        $this->descriptor->describe($output, new \stdClass(), array(
            'manPath' => __DIR__.'/foobar.1',
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDescribeFailsIfManNotFound()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->once())
            ->method('find')
            ->with('man')
            ->will($this->returnValue(null));

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $this->descriptor->describe($output, new \stdClass(), array(
            'manPath' => __DIR__.'/Fixtures/man/package.1',
        ));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDescribeFailsIfCustomManBinaryNotFound()
    {
        $output = new BufferedOutput();

        $this->executableFinder->expects($this->never())
            ->method('find');

        $this->processLauncher->expects($this->never())
            ->method('launchProcess');

        $this->descriptor->describe($output, new \stdClass(), array(
            'manPath' => __DIR__.'/Fixtures/man/package.1',
            'manBinary' => false,
        ));
    }
}

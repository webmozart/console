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
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Output\Output;
use Webmozart\Console\Descriptor\DelegatingDescriptor;
use Webmozart\Console\Descriptor\Descriptor;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DelegatingDescriptorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DelegatingDescriptor
     */
    private $descriptor;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Output
     */
    private $output;

    /**
     * @var \stdClass
     */
    private $object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Descriptor
     */
    private $delegate1;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|Descriptor
     */
    private $delegate2;

    /**
     * @var ArgsFormat
     */
    private $argsFormat;

    protected function setUp()
    {
        $this->descriptor = new DelegatingDescriptor();
        $this->output = $this->getMock('Webmozart\Console\Api\Output\Output');
        $this->object = new \stdClass();
        $this->delegate1 = $this->getMock('Webmozart\Console\Descriptor\Descriptor');
        $this->delegate2 = $this->getMock('Webmozart\Console\Descriptor\Descriptor');
        $this->argsFormat = new ArgsFormat(array(
            new Option('format', null, Option::REQUIRED_VALUE),
        ));
    }

    public function testDescribe()
    {
        $args = new Args($this->argsFormat);
        $args->setOption('format', 'xml');
        $options = array('args' => $args);

        $this->descriptor->register('text', $this->delegate1);
        $this->descriptor->register('xml', $this->delegate2);

        $this->delegate1->expects($this->never())
            ->method('describe');

        $this->delegate2->expects($this->once())
            ->method('describe')
            ->with($this->output, $this->object, $options)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $this->object, $options);

        $this->assertSame(123, $status);
    }

    public function testDescribeUsesFirstRegisteredDescriptorByDefault()
    {
        $args = new Args($this->argsFormat);
        $options = array('args' => $args);

        $this->descriptor->register('text', $this->delegate1);
        $this->descriptor->register('xml', $this->delegate2);

        $this->delegate1->expects($this->once())
            ->method('describe')
            ->with($this->output, $this->object, $options)
            ->will($this->returnValue(123));

        $this->delegate2->expects($this->never())
            ->method('describe');

        $status = $this->descriptor->describe($this->output, $this->object, $options);

        $this->assertSame(123, $status);
    }

    public function testDescribeUsesDefaultDescriptorPassedToConstructor()
    {
        $args = new Args($this->argsFormat);
        $args->setOption('format', 'xml');
        $options = array('args' => $args);

        $this->descriptor = new DelegatingDescriptor('xml');
        $this->descriptor->register('text', $this->delegate1);
        $this->descriptor->register('xml', $this->delegate2);

        $this->delegate1->expects($this->never())
            ->method('describe');

        $this->delegate2->expects($this->once())
            ->method('describe')
            ->with($this->output, $this->object, $options)
            ->will($this->returnValue(123));

        $status = $this->descriptor->describe($this->output, $this->object, $options);

        $this->assertSame(123, $status);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testDescribeFailsIfFormatNotSupported()
    {
        $args = new Args($this->argsFormat);
        $args->setOption('format', 'xml');
        $options = array('args' => $args);

        $this->descriptor->describe($this->output, $this->object, $options);
    }
}

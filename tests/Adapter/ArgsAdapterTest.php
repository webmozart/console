<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Adapter;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Adapter\ArgsAdapter;
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Args\Format\ArgsFormat;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\IO\Input;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\IO\Input\BufferedInput;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ArgsAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RawArgs
     */
    private $rawArgs;

    /**
     * @var Args
     */
    private $args;

    protected function setUp()
    {
        $this->rawArgs = new StringArgs('');
        $this->args = new Args(new ArgsFormat(array(
            new Argument('argument1'),
            new Argument('argument2', 0, null, 'default'),
            new Option('option1', 'o', Option::NO_VALUE),
            new Option('option2', null, Option::OPTIONAL_VALUE, null, 'default'),
        )));
    }

    public function testCreate()
    {
        $input = new ArgsAdapter($this->rawArgs, $this->args);

        $this->assertSame($this->rawArgs, $input->getRawArgs());
        $this->assertSame($this->args, $input->getArgs());
    }

    public function testCreateNoArgs()
    {
        $input = new ArgsAdapter($this->rawArgs);

        $this->assertSame($this->rawArgs, $input->getRawArgs());
        $this->assertNull($input->getArgs());
    }

    public function testGetFirstArgument()
    {
        $input = new ArgsAdapter(new StringArgs('one -o two --option three'));

        $this->assertSame('one', $input->getFirstArgument());
    }

    public function testGetNoFirstArgument()
    {
        $input = new ArgsAdapter(new StringArgs(''));

        $this->assertNull($input->getFirstArgument());
    }

    public function testHasParameterOption()
    {
        $input = new ArgsAdapter(new StringArgs('-o --option --value=value'));

        $this->assertTrue($input->hasParameterOption('-o'));
        $this->assertTrue($input->hasParameterOption('--option'));
        $this->assertTrue($input->hasParameterOption('--value'));
        $this->assertFalse($input->hasParameterOption('--foo'));
    }

    public function testHasMultipleParameterOptions()
    {
        $input = new ArgsAdapter(new StringArgs('-o --option --value=value'));

        $this->assertTrue($input->hasParameterOption(array('-o', '--option')));
        // sufficient if any of the options exists
        $this->assertTrue($input->hasParameterOption(array('-o', '--foo')));
        $this->assertFalse($input->hasParameterOption(array('--foo', '--bar')));
    }

    public function testGetParameterOption()
    {
        $input = new ArgsAdapter(new StringArgs('-vvalue1  --value=value2 --space value3 --last'));

        $this->assertSame('value1', $input->getParameterOption('-v'));
        $this->assertSame('value2', $input->getParameterOption('--value'));
        $this->assertSame('value3', $input->getParameterOption('--space'));
        $this->assertNull($input->getParameterOption('--last'));
        $this->assertSame('default', $input->getParameterOption('--foo', 'default'));
    }

    public function testGetArguments()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $this->args->setArguments(array(
            'argument1' => 'value1',
        ));

        $this->assertSame(array(
            'argument1' => 'value1',
            'argument2' => 'default',
        ), $inputArgs->getArguments());

        $this->assertSame(array(), $inputNoArgs->getArguments());
    }

    public function testGetArgument()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $this->args->setArguments(array(
            'argument1' => 'value1',
        ));

        $this->assertSame('value1', $inputArgs->getArgument('argument1'));
        $this->assertSame('default', $inputArgs->getArgument('argument2'));
        $this->assertNull($inputNoArgs->getArgument('argument1'));
    }

    public function testSetArgument()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $inputArgs->setArgument('argument1', 'value1');
        $inputNoArgs->setArgument('argument1', 'value1');

        $this->assertSame('value1', $inputArgs->getArgument('argument1'));
        $this->assertNull($inputNoArgs->getArgument('argument1'));
    }

    public function testHasArgument()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $this->args->setArguments(array(
            'argument1' => 'value1',
        ));

        $this->assertTrue($inputArgs->hasArgument('argument1'));
        $this->assertTrue($inputArgs->hasArgument('argument2'));
        $this->assertFalse($inputArgs->hasArgument('argument3'));
        $this->assertFalse($inputNoArgs->hasArgument('argument1'));
    }

    public function testGetOptions()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $this->args->setOptions(array(
            'option1' => true,
        ));

        $this->assertSame(array(
            'option1' => true,
            'option2' => 'default',
        ), $inputArgs->getOptions());

        $this->assertSame(array(), $inputNoArgs->getOptions());
    }

    public function testGetOption()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $this->args->setOptions(array(
            'option1' => true,
        ));

        $this->assertTrue($inputArgs->getOption('option1'));
        $this->assertSame('default', $inputArgs->getOption('option2'));
        $this->assertNull($inputNoArgs->getOption('option1'));
    }

    public function testSetOption()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $inputArgs->setOption('option2', 'value1');
        $inputNoArgs->setOption('option2', 'value1');

        $this->assertSame('value1', $inputArgs->getOption('option2'));
        $this->assertNull($inputNoArgs->getOption('option2'));
    }

    public function testHasOption()
    {
        $inputArgs = new ArgsAdapter($this->rawArgs, $this->args);
        $inputNoArgs = new ArgsAdapter($this->rawArgs);

        $this->args->setOptions(array(
            'option1' => true,
        ));

        $this->assertTrue($inputArgs->hasOption('option1'));
        $this->assertTrue($inputArgs->hasOption('option2'));
        $this->assertFalse($inputArgs->hasOption('option3'));
        $this->assertFalse($inputNoArgs->hasOption('option1'));
    }
}

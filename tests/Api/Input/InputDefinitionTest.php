<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Api\Input;

use PHPUnit_Framework_TestCase;
use stdClass;
use Webmozart\Console\Api\Input\InputArgument;
use Webmozart\Console\Api\Input\InputDefinition;
use Webmozart\Console\Api\Input\InputOption;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InputDefinitionTest extends PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $definition = new InputDefinition();

        $this->assertSame(array(), $definition->getArguments());
        $this->assertSame(array(), $definition->getOptions());
        $this->assertSame(array(), $definition->getDefaultArgumentValues());
        $this->assertSame(array(), $definition->getDefaultOptionValues());
        $this->assertSame(0, $definition->getNumberOfArguments());
        $this->assertSame(0, $definition->getNumberOfRequiredArguments());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage stdClass
     */
    public function testCreateFailsIfNeitherOptionNorArgument()
    {
        new InputDefinition(array(new stdClass()));
    }

    public function testCreateWithElements()
    {
        $definition = new InputDefinition(array(
            $argument = new InputArgument('argument'),
            $option = new InputOption('option'),
        ));

        $this->assertSame(array('argument' => $argument), $definition->getArguments());
        $this->assertSame(array('option' => $option), $definition->getOptions());
        $this->assertSame(1, $definition->getNumberOfArguments());
        $this->assertSame(0, $definition->getNumberOfRequiredArguments());
    }

    public function testGetArgument()
    {
        $definition = new InputDefinition(array(
            $argument1 = new InputArgument('argument1'),
            $argument2 = new InputArgument('argument2'),
        ));

        $this->assertSame($argument1, $definition->getArgument('argument1'));
        $this->assertSame($argument2, $definition->getArgument('argument2'));
    }

    public function testGetArgumentFromBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $argument = new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertSame($argument, $definition->getArgument('argument'));
    }

    public function testGetArgumentPrefersOverriddenArgument()
    {
        $baseDefinition = new InputDefinition(array(
            $argument1 = new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(
            $argument2 = new InputArgument('argument'),
        ), $baseDefinition);

        $this->assertSame($argument2, $definition->getArgument('argument'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfUnknownName()
    {
        $definition = new InputDefinition();
        $definition->getArgument('foobar');
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foobar
     */
    public function testGetArgumentFailsIfInBaseDefinitionButIncludeBaseDisabled()
    {
        $baseDefinition = new InputDefinition(array(
            $argument = new InputArgument('foobar'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $definition->getArgument('foobar', false);
    }

    public function testGetArgumentByPosition()
    {
        $definition = new InputDefinition(array(
            $argument1 = new InputArgument('argument1'),
            $argument2 = new InputArgument('argument2'),
        ));

        $this->assertSame($argument1, $definition->getArgument(0));
        $this->assertSame($argument2, $definition->getArgument(1));
    }

    public function testGetArgumentByPositionFromBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $argument = new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertSame($argument, $definition->getArgument(0));
    }

    public function testGetArgumentByPositionPrefersOverriddenArgument()
    {
        $baseDefinition = new InputDefinition(array(
            $argument1 = new InputArgument('argument1'),
            $argument2 = new InputArgument('argument2'),
        ));
        $definition = new InputDefinition(array(
            $argument3 = new InputArgument('argument1'),
        ), $baseDefinition);

        $this->assertSame($argument3, $definition->getArgument(0));
        $this->assertSame($argument2, $definition->getArgument(1));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage 0
     */
    public function testGetArgumentByPositionFailsIfUnknownPosition()
    {
        $definition = new InputDefinition();
        $definition->getArgument(0);
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage 0
     */
    public function testGetArgumentByPositionFailsIfInBaseDefinitionButIncludeBaseDisabled()
    {
        $baseDefinition = new InputDefinition(array(
            $argument = new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $definition->getArgument(0, false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfNull()
    {
        $definition = new InputDefinition();
        $definition->getArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfEmpty()
    {
        $definition = new InputDefinition();
        $definition->getArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfNoString()
    {
        $definition = new InputDefinition();
        $definition->getArgument(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getArgument('argument', 1234);
    }

    public function testGetArguments()
    {
        $definition = new InputDefinition(array(
            $argument1 = new InputArgument('argument1'),
            $argument2 = new InputArgument('argument2'),
        ));

        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
        ), $definition->getArguments());
    }

    public function testGetArgumentsWithBaseArguments()
    {
        $baseDefinition = new InputDefinition(array(
            $argument1 = new InputArgument('argument1'),
        ));
        $definition = new InputDefinition(array(
            $argument2 = new InputArgument('argument2'),
        ), $baseDefinition);

        $this->assertSame(array(
            'argument1' => $argument1,
            'argument2' => $argument2,
        ), $definition->getArguments());

        $this->assertSame(array(
            'argument2' => $argument2,
        ), $definition->getArguments(false));
    }

    public function testGetArgumentsPrefersOverriddenArguments()
    {
        $baseDefinition = new InputDefinition(array(
            $argument1 = new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(
            $argument2 = new InputArgument('argument'),
        ), $baseDefinition);

        $this->assertSame(array(
            'argument' => $argument2,
        ), $definition->getArguments());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getArguments(1234);
    }

    public function testHasArgument()
    {
        $definition = new InputDefinition();

        $this->assertFalse($definition->hasArgument('argument'));
        $this->assertFalse($definition->hasArgument('argument', false));

        $definition = new InputDefinition(array(
            new InputArgument('argument'),
        ));

        $this->assertTrue($definition->hasArgument('argument'));
        $this->assertTrue($definition->hasArgument('argument', false));
    }

    public function testHasArgumentWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $argument1 = new InputArgument('argument1'),
        ));
        $definition = new InputDefinition(array(
            $argument2 = new InputArgument('argument2'),
        ), $baseDefinition);

        $this->assertTrue($definition->hasArgument('argument1'));
        $this->assertTrue($definition->hasArgument('argument2'));

        $this->assertFalse($definition->hasArgument('argument1', false));
        $this->assertTrue($definition->hasArgument('argument2', false));
    }

    public function testHasArgumentAtPosition()
    {
        $definition = new InputDefinition();

        $this->assertFalse($definition->hasArgument(0));
        $this->assertFalse($definition->hasArgument(0, false));

        $definition = new InputDefinition(array(
            new InputArgument('argument'),
        ));

        $this->assertTrue($definition->hasArgument(0));
        $this->assertTrue($definition->hasArgument(0, false));
    }

    public function testHasArgumentAtPositionWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument1'),
        ));
        $definition = new InputDefinition(array(
            new InputArgument('argument2'),
        ), $baseDefinition);

        $this->assertTrue($definition->hasArgument(0));
        $this->assertTrue($definition->hasArgument(1));

        $this->assertTrue($definition->hasArgument(0, false));
        $this->assertFalse($definition->hasArgument(1, false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfNull()
    {
        $definition = new InputDefinition();
        $definition->hasArgument(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfEmpty()
    {
        $definition = new InputDefinition();
        $definition->hasArgument('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfNoString()
    {
        $definition = new InputDefinition();
        $definition->hasArgument(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasArgument('argument', 1234);
    }

    public function testHasArguments()
    {
        $definition = new InputDefinition();
        $this->assertFalse($definition->hasArguments());
        $this->assertFalse($definition->hasArguments(false));

        $definition = new InputDefinition(array(
            new InputArgument('argument'),
        ));
        $this->assertTrue($definition->hasArguments());
        $this->assertTrue($definition->hasArguments(false));
    }

    public function testHasArgumentsWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertTrue($definition->hasArguments());
        $this->assertFalse($definition->hasArguments(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasArguments(1234);
    }

    public function testHasMultiValuedArgument()
    {
        $definition = new InputDefinition();
        $this->assertFalse($definition->hasMultiValuedArgument());
        $this->assertFalse($definition->hasMultiValuedArgument(false));

        $definition = new InputDefinition(array(new InputArgument('argument')));
        $this->assertFalse($definition->hasMultiValuedArgument());
        $this->assertFalse($definition->hasMultiValuedArgument(false));

        $definition = new InputDefinition(array(new InputArgument('argument', InputArgument::MULTI_VALUED)));
        $this->assertTrue($definition->hasMultiValuedArgument());
        $this->assertTrue($definition->hasMultiValuedArgument(false));
    }

    public function testHasMultiValuedArgumentWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument', InputArgument::MULTI_VALUED),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertTrue($definition->hasMultiValuedArgument());
        $this->assertFalse($definition->hasMultiValuedArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasMultiValuedArgumentFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasMultiValuedArgument(1234);
    }

    public function testHasOptionalArgument()
    {
        $definition = new InputDefinition();
        $this->assertFalse($definition->hasOptionalArgument());
        $this->assertFalse($definition->hasOptionalArgument(false));

        $definition = new InputDefinition(array(new InputArgument('argument', InputArgument::REQUIRED)));
        $this->assertFalse($definition->hasOptionalArgument());
        $this->assertFalse($definition->hasOptionalArgument(false));

        $definition = new InputDefinition(array(new InputArgument('argument', InputArgument::OPTIONAL)));
        $this->assertTrue($definition->hasOptionalArgument());
        $this->assertTrue($definition->hasOptionalArgument(false));
    }

    public function testHasOptionalArgumentWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument', InputArgument::OPTIONAL),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertTrue($definition->hasOptionalArgument());
        $this->assertFalse($definition->hasOptionalArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionalArgumentFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasOptionalArgument(1234);
    }

    public function testHasRequiredArgument()
    {
        $definition = new InputDefinition();
        $this->assertFalse($definition->hasRequiredArgument());
        $this->assertFalse($definition->hasRequiredArgument(false));

        $definition = new InputDefinition(array(new InputArgument('argument', InputArgument::OPTIONAL)));
        $this->assertFalse($definition->hasRequiredArgument());
        $this->assertFalse($definition->hasRequiredArgument(false));

        $definition = new InputDefinition(array(new InputArgument('argument', InputArgument::REQUIRED)));
        $this->assertTrue($definition->hasRequiredArgument());
        $this->assertTrue($definition->hasRequiredArgument(false));
    }

    public function testHasRequiredArgumentWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument', InputArgument::REQUIRED),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertTrue($definition->hasRequiredArgument());
        $this->assertFalse($definition->hasRequiredArgument(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasRequiredArgumentFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasRequiredArgument(1234);
    }

    public function testGetNumberOfArguments()
    {
        $definition = new InputDefinition();

        $this->assertSame(0, $definition->getNumberOfArguments());

        $definition = new InputDefinition(array(
            new InputArgument('argument1'),
            new InputArgument('argument2'),
        ));

        $this->assertSame(2, $definition->getNumberOfArguments());
    }

    public function testGetNumberOfArgumentsWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument1'),
        ));
        $definition = new InputDefinition(array(
            new InputArgument('argument2'),
        ), $baseDefinition);

        $this->assertSame(2, $definition->getNumberOfArguments());
        $this->assertSame(1, $definition->getNumberOfArguments(false));
    }

    public function testGetNumberOfArgumentsCountsDuplicateArgumentsOnlyOnce()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument'),
        ));
        $definition = new InputDefinition(array(
            new InputArgument('argument'),
        ), $baseDefinition);

        $this->assertSame(1, $definition->getNumberOfArguments());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNumberOfArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getNumberOfArguments(1234);
    }

    public function testGetNumberOfRequiredArguments()
    {
        $definition = new InputDefinition();

        $this->assertSame(0, $definition->getNumberOfRequiredArguments());

        $definition = new InputDefinition(array(
            new InputArgument('argument1', InputArgument::REQUIRED),
            new InputArgument('argument2', InputArgument::REQUIRED),
            new InputArgument('argument3'),
        ));

        $this->assertSame(2, $definition->getNumberOfRequiredArguments());
    }

    public function testGetNumberOfRequiredArgumentsWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument1', InputArgument::REQUIRED),
        ));
        $definition = new InputDefinition(array(
            new InputArgument('argument2', InputArgument::REQUIRED),
            new InputArgument('argument3'),
        ), $baseDefinition);

        $this->assertSame(2, $definition->getNumberOfRequiredArguments());
        $this->assertSame(1, $definition->getNumberOfRequiredArguments(false));
    }

    public function testGetNumberOfRequiredArgumentsCountsDuplicateArgumentsOnlyOnce()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument', InputArgument::REQUIRED),
        ));
        $definition = new InputDefinition(array(
            new InputArgument('argument', InputArgument::REQUIRED),
        ), $baseDefinition);

        $this->assertSame(1, $definition->getNumberOfRequiredArguments());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetNumberOfRequiredArgumentsFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getNumberOfRequiredArguments(1234);
    }

    public function testGetDefaultArgumentValues()
    {
        $definition = new InputDefinition(array(
            new InputArgument('argument1', 0, null, 'Default'),
            new InputArgument('argument2'),
        ));

        $this->assertSame(array(
            'argument1' => 'Default',
            'argument2' => null,
        ), $definition->getDefaultArgumentValues());

        $this->assertSame(array(
            'argument1' => 'Default',
            'argument2' => null,
        ), $definition->getDefaultArgumentValues(false));
    }

    public function testGetDefaultArgumentValuesWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputArgument('argument1', 0, null, 'Default'),
        ));
        $definition = new InputDefinition(array(
            new InputArgument('argument2'),
        ), $baseDefinition);

        $this->assertSame(array(
            'argument1' => 'Default',
            'argument2' => null,
        ), $definition->getDefaultArgumentValues());

        $this->assertSame(array(
            'argument2' => null,
        ), $definition->getDefaultArgumentValues(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDefaultArgumentValuesFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getDefaultArgumentValues(1234);
    }

    public function testGetOptions()
    {
        $definition = new InputDefinition(array(
            $option1 = new InputOption('option1'),
            $option2 = new InputOption('option2'),
        ));

        $this->assertSame(array('option1' => $option1, 'option2' => $option2), $definition->getOptions());
    }

    public function testGetOptionsWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $option1 = new InputOption('option1'),
        ));
        $definition = new InputDefinition(array(
            $option2 = new InputOption('option2'),
            $option3 = new InputOption('option3'),
        ), $baseDefinition);

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
            'option1' => $option1,
        ), $definition->getOptions());

        $this->assertSame(array(
            'option2' => $option2,
            'option3' => $option3,
        ), $definition->getOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionsFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getOptions(1234);
    }

    public function testGetOption()
    {
        $definition = new InputDefinition(array(
            $option = new InputOption('option'),
        ));

        $this->assertSame($option, $definition->getOption('option'));
    }

    public function testGetOptionFromBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $option = new InputOption('option'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertSame($option, $definition->getOption('option'));
    }

    public function testGetOptionByShortName()
    {
        $definition = new InputDefinition(array(
            $option = new InputOption('option', 'o'),
        ));

        $this->assertSame($option, $definition->getOption('o'));
    }

    public function testGetOptionByShortNameFromBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $option = new InputOption('option', 'o'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertSame($option, $definition->getOption('o'));
    }

    public function testGetOptionPrefersOverriddenOption()
    {
        $baseDefinition = new InputDefinition(array(
            $option1 = new InputOption('option'),
        ));
        $definition = new InputDefinition(array(
            $option2 = new InputOption('option', null, 0, 'Refined description'),
        ), $baseDefinition);

        $this->assertSame($option2, $definition->getOption('option'));
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfUnknownName()
    {
        $definition = new InputDefinition();
        $definition->getOption('foobar');
    }

    /**
     * @expectedException \OutOfBoundsException
     * @expectedExceptionMessage foobar
     */
    public function testGetOptionFailsIfInBaseDefinitionButIncludeBaseDisabled()
    {
        $baseDefinition = new InputDefinition(array(
            $option = new InputOption('foobar'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $definition->getOption('foobar', false);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfNull()
    {
        $definition = new InputDefinition();
        $definition->getOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfEmpty()
    {
        $definition = new InputDefinition();
        $definition->getOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfNoString()
    {
        $definition = new InputDefinition();
        $definition->getOption(1234);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetOptionFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getOption('argument', 1234);
    }

    public function testHasOption()
    {
        $definition = new InputDefinition();
        $this->assertFalse($definition->hasOption('option'));
        $this->assertFalse($definition->hasOption('option', false));

        $definition = new InputDefinition(array(new InputOption('option')));
        $this->assertTrue($definition->hasOption('option'));
        $this->assertTrue($definition->hasOption('option', false));
    }

    public function testHasOptionWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $option1 = new InputOption('option1'),
        ));
        $definition = new InputDefinition(array(
            $option2 = new InputOption('option2'),
        ), $baseDefinition);

        $this->assertTrue($definition->hasOption('option1'));
        $this->assertFalse($definition->hasOption('option1', false));

        $this->assertTrue($definition->hasOption('option2'));
        $this->assertTrue($definition->hasOption('option2', false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfNull()
    {
        $definition = new InputDefinition();
        $definition->hasOption(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfEmpty()
    {
        $definition = new InputDefinition();
        $definition->hasOption('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfNoString()
    {
        $definition = new InputDefinition();
        $definition->hasOption(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasOption('option', 1234);
    }

    public function testHasOptions()
    {
        $definition = new InputDefinition();
        $this->assertFalse($definition->hasOptions());
        $this->assertFalse($definition->hasOptions(false));

        $definition = new InputDefinition(array(new InputOption('option')));
        $this->assertTrue($definition->hasOptions());
        $this->assertTrue($definition->hasOptions(false));
    }

    public function testHasOptionsWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            $option = new InputOption('option'),
        ));
        $definition = new InputDefinition(array(), $baseDefinition);

        $this->assertTrue($definition->hasOptions());
        $this->assertFalse($definition->hasOptions(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasOptionsFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->hasOptions(1234);
    }

    public function testGetDefaultOptionValues()
    {
        $definition = new InputDefinition(array(
            new InputOption('option1', null, InputOption::VALUE_OPTIONAL, null, 'Default'),
            new InputOption('option2', null),
        ));

        $this->assertSame(array(
            'option1' => 'Default',
            'option2' => null,
        ), $definition->getDefaultOptionValues());

        $this->assertSame(array(
            'option1' => 'Default',
            'option2' => null,
        ), $definition->getDefaultOptionValues(false));
    }

    public function testGetDefaultOptionValuesWithBaseDefinition()
    {
        $baseDefinition = new InputDefinition(array(
            new InputOption('option1', null, InputOption::VALUE_OPTIONAL, null, 'Default'),
        ));
        $definition = new InputDefinition(array(
            new InputOption('option2', null),
        ), $baseDefinition);

        $this->assertSame(array(
            'option2' => null,
            'option1' => 'Default',
        ), $definition->getDefaultOptionValues());

        $this->assertSame(array(
            'option2' => null,
        ), $definition->getDefaultOptionValues(false));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetDefaultOptionValuesFailsIfIncludeBaseNoBoolean()
    {
        $definition = new InputDefinition();
        $definition->getDefaultOptionValues(1234);
    }
}

<?php

/*
 * This file is part of the webmozart/console package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\Console\Tests\Resolver;

use PHPUnit_Framework_TestCase;
use Webmozart\Console\Api\Args\Format\Argument;
use Webmozart\Console\Api\Args\RawArgs;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Resolver\ResolvedCommand;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Resolver\HelpAwareResolver;
use Webmozart\Console\Resolver\ResolveResult;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class HelpAwareResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ConsoleApplication
     */
    private static $application;

    /**
     * @var HelpAwareResolver
     */
    private $resolver;

    public static function setUpBeforeClass()
    {
        $config = ApplicationConfig::create()
            ->addOption('help', 'h')

            ->beginCommand('help')
                ->addArgument('arg', Argument::MULTI_VALUED)
            ->end()

            ->beginCommand('default')
                ->markDefault()
            ->end()
        ;

        self::$application = new ConsoleApplication($config);
    }

    protected function setUp()
    {
        $this->resolver = new HelpAwareResolver('help', array('-h', '--help'));
    }

    /**
     * @dataProvider getArgsWithMatchingTokens
     */
    public function testReturnHelpIfAnyTokenPresent(RawArgs $args)
    {
        $helpCommand = self::$application->getCommand('help');
        $parsedArgs = $helpCommand->parseArgs($args);

        $resolvedCommand = $this->resolver->resolveCommand($args, self::$application);

        $this->assertEquals(new ResolvedCommand($helpCommand, $parsedArgs), $resolvedCommand);
    }

    public function getArgsWithMatchingTokens()
    {
        return array(
            array(new StringArgs('-h')),
            array(new StringArgs('--help')),
            array(new StringArgs('default -h')),
            array(new StringArgs('--help default')),
        );
    }

    /**
     * @dataProvider getArgsWithNoMatchingTokens
     */
    public function testReturnDefaultCommandIfNoTokenPresent(RawArgs $args)
    {
        $defaultCommand = self::$application->getCommand('default');
        $parsedArgs = $defaultCommand->parseArgs($args);

        $resolvedCommand = $this->resolver->resolveCommand($args, self::$application);

        $this->assertEquals(new ResolvedCommand($defaultCommand, $parsedArgs), $resolvedCommand);
    }

    public function getArgsWithNoMatchingTokens()
    {
        return array(
            array(new StringArgs('')),
            array(new StringArgs('default')),
        );
    }
}

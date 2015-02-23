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
use Webmozart\Console\Adapter\InputInterfaceAdapter;
use Webmozart\Console\Api\Args\Format\Option;
use Webmozart\Console\Api\Config\ApplicationConfig;
use Webmozart\Console\Api\Resolver\CannotResolveCommandException;
use Webmozart\Console\Args\StringArgs;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Resolver\DefaultResolver;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ConsoleApplication
     */
    private static $application;

    /**
     * @var DefaultResolver
     */
    private $resolver;

    public static function setUpBeforeClass()
    {
        $config = ApplicationConfig::create()
            ->addOption('option', 'o')
            ->addOption('value', 'v', Option::OPTIONAL_VALUE)
            ->addArgument('arg')

            ->addDefaultCommandName('default')

            ->beginCommand('package')
                ->addAlias('package-alias')
                ->beginSubCommand('add')
                    ->addAlias('add-alias')
                ->end()
                ->beginSubCommand('addon')->end()
                ->beginOptionCommand('delete', 'd')
                    ->addAlias('delete-alias')
                ->end()
                ->beginOptionCommand('delete-all')->end()
            ->end()

            ->beginCommand('pack')->end()

            ->beginCommand('default')->end()

            ->beginCommand('stash')
                ->beginSubCommand('save')
                    ->beginOptionCommand('do', 'D')->end()
                ->end()
                ->addDefaultCommandName('save')
            ->end()

            ->beginCommand('server')
                ->beginOptionCommand('list')->end()
                ->addDefaultCommandName('list')
            ->end()

            ->beginCommand('bind')
                ->beginSubCommand('list')
                    ->beginOptionCommand('do', 'D')->end()
                ->end()
                ->beginSubCommand('add')
                    ->addArgument('binding')
                    ->beginOptionCommand('do', 'D')->end()
                ->end()
                ->addDefaultCommandNames(array('list', 'add'))
            ->end()
        ;

        self::$application = new ConsoleApplication($config);
    }

    protected function setUp()
    {
        $this->resolver = new DefaultResolver('default');
    }

    /**
     * @dataProvider getInputOutputTests
     */
    public function testResolveCommand($inputString, $commandName)
    {
        $resolvedCommand = $this->resolver->resolveCommand(new StringArgs($inputString), self::$application);

        $this->assertInstanceOf('Webmozart\Console\Api\Resolver\ResolvedCommand', $resolvedCommand);
        $this->assertSame($commandName, $resolvedCommand->getCommand()->getName());
    }

    public function getInputOutputTests()
    {
        return array(
            // no options
            array('package', 'package'),
            array('package arg', 'package'),
            array('pack', 'pack'),
            array('pack arg', 'pack'),
            array('package add', 'add'),
            array('package add arg', 'add'),
            array('package addon', 'addon'),
            array('package addon arg', 'addon'),

            // options with simple command
            array('package -o', 'package'),
            array('package --option', 'package'),
            array('package -v1', 'package'),
            array('package -v 1', 'package'),
            array('package --value="1"', 'package'),
            array('package --value=\'1\'', 'package'),

            // options+args with simple command
            array('package -o arg', 'package'),
            array('package --option arg', 'package'),
            array('package -v1 arg', 'package'),
            array('package -v 1 arg', 'package'),
            array('package --value="1" arg', 'package'),
            array('package --value=\'1\' arg', 'package'),

            // options before sub-command not possible
            array('package -o add', 'package'),
            array('package --option add', 'package'),
            array('package -v1 add', 'package'),
            array('package -v 1 add', 'package'),
            array('package --value="1" add', 'package'),
            array('package --value=\'1\' add', 'package'),

            // options after sub-command
            array('package add -o', 'add'),
            array('package add --option', 'add'),
            array('package add -v1', 'add'),
            array('package add -v 1', 'add'),
            array('package add --value="1"', 'add'),
            array('package add --value=\'1\'', 'add'),

            // options+args after sub-command
            array('package add -o arg', 'add'),
            array('package add --option arg', 'add'),
            array('package add -v1 arg', 'add'),
            array('package add -v 1 arg', 'add'),
            array('package add --value="1" arg', 'add'),
            array('package add --value=\'1\' arg', 'add'),

            // options before long option command
            array('package -o --delete', 'delete'),
            array('package --option --delete', 'delete'),
            array('package -v1 --delete', 'delete'),
            array('package -v 1 --delete', 'delete'),
            array('package --value="1" --delete', 'delete'),
            array('package --value=\'1\' --delete', 'delete'),

            // options before short option command
            array('package -o -d', 'delete'),
            array('package --option -d', 'delete'),
            array('package -v1 -d', 'delete'),
            array('package -v 1 -d', 'delete'),
            array('package --value="1" -d', 'delete'),
            array('package --value=\'1\' -d', 'delete'),

            // options after long option command
            array('package --delete -o', 'delete'),
            array('package --delete --option', 'delete'),
            array('package --delete -v1', 'delete'),
            array('package --delete -v 1', 'delete'),
            array('package --delete --value="1"', 'delete'),
            array('package --delete --value=\'1\'', 'delete'),

            // options after short option command
            array('package -d -o', 'delete'),
            array('package -d --option', 'delete'),
            array('package -d -v1', 'delete'),
            array('package -d -v 1', 'delete'),
            array('package -d --value="1"', 'delete'),
            array('package -d --value=\'1\'', 'delete'),

            // options+args after long option command
            array('package --delete -o arg', 'delete'),
            array('package --delete --option arg', 'delete'),
            array('package --delete -v1 arg', 'delete'),
            array('package --delete -v 1 arg', 'delete'),
            array('package --delete --value="1" arg', 'delete'),
            array('package --delete --value=\'1\' arg', 'delete'),

            // options+args after short option command
            array('package -d -o arg', 'delete'),
            array('package -d --option arg', 'delete'),
            array('package -d -v1 arg', 'delete'),
            array('package -d -v 1 arg', 'delete'),
            array('package -d --value="1" arg', 'delete'),
            array('package -d --value=\'1\' arg', 'delete'),

            // aliases
            array('package-alias', 'package'),
            array('package-alias arg', 'package'),
            array('package add-alias', 'add'),
            array('package add-alias arg', 'add'),
//            array('package --delete-alias', 'delete'),
//            array('package --delete-alias arg', 'delete'),

            // aliases with options
            array('package-alias -o', 'package'),
            array('package-alias --option', 'package'),
            array('package-alias -v1', 'package'),
            array('package-alias -v 1', 'package'),
            array('package-alias --value="1"', 'package'),
            array('package-alias --value=\'1\'', 'package'),

            array('package-alias -o arg', 'package'),
            array('package-alias --option arg', 'package'),
            array('package-alias -v1 arg', 'package'),
            array('package-alias -v 1 arg', 'package'),
            array('package-alias --value="1" arg', 'package'),
            array('package-alias --value=\'1\' arg', 'package'),

            array('package add-alias -o', 'add'),
            array('package add-alias --option', 'add'),
            array('package add-alias -v1', 'add'),
            array('package add-alias -v 1', 'add'),
            array('package add-alias --value="1"', 'add'),
            array('package add-alias --value=\'1\'', 'add'),

            array('package add-alias -o arg', 'add'),
            array('package add-alias --option arg', 'add'),
            array('package add-alias -v1 arg', 'add'),
            array('package add-alias -v 1 arg', 'add'),
            array('package add-alias --value="1" arg', 'add'),
            array('package add-alias --value=\'1\' arg', 'add'),

//            array('package --delete-alias -o', 'delete'),
//            array('package --delete-alias --option', 'delete'),
//            array('package --delete-alias -v1', 'delete'),
//            array('package --delete-alias -v 1', 'delete'),
//            array('package --delete-alias --value="1"', 'delete'),
//            array('package --delete-alias --value=\'1\'', 'delete'),
//
//            array('package --delete-alias -o arg', 'delete'),
//            array('package --delete-alias --option arg', 'delete'),
//            array('package --delete-alias -v1 arg', 'delete'),
//            array('package --delete-alias -v 1 arg', 'delete'),
//            array('package --delete-alias --value="1" arg', 'delete'),
//            array('package --delete-alias --value=\'1\' arg', 'delete'),

            // regex special chars
            array('package *', 'package'),
            array('package **', 'package'),
            array('package /app/*', 'package'),
            array('package /app/**', 'package'),
            array('package -v * arg', 'package'),
            array('package -v ** arg', 'package'),
            array('package -v /app/* arg', 'package'),
            array('package -v /app/** arg', 'package'),
            array('package add *', 'add'),
            array('package add **', 'add'),
            array('package add /app/*', 'add'),
            array('package add /app/**', 'add'),
            array('package add -v *', 'add'),
            array('package add -v **', 'add'),
            array('package add -v /app/*', 'add'),
            array('package add -v /app/**', 'add'),
            array('package --delete *', 'delete'),
            array('package --delete **', 'delete'),
            array('package --delete /app/*', 'delete'),
            array('package --delete /app/**', 'delete'),
            array('package --delete -v *', 'delete'),
            array('package --delete -v **', 'delete'),
            array('package --delete -v /app/*', 'delete'),
            array('package --delete -v /app/**', 'delete'),

            // stop option parsing after "--"
            array('package -- --delete', 'package'),
            array('package -- -d', 'package'),
            array('package -- add', 'package'),

            // default command
            array('', 'default'),

            // options with default command
            array('-o', 'default'),
            array('--option', 'default'),
            array('-v1', 'default'),
            array('-v 1', 'default'),
            array('--value="1"', 'default'),
            array('--value=\'1\'', 'default'),

            // options+args with default command
            array('-o arg', 'default'),
            array('--option arg', 'default'),
            array('-v1 arg', 'default'),
            array('-v 1 arg', 'default'),
            array('--value="1" arg', 'default'),
            array('--value=\'1\' arg', 'default'),

            // default sub command
            array('stash', 'save'),

            // options with default sub command
            array('stash -o', 'save'),
            array('stash --option', 'save'),
            array('stash -v1', 'save'),
            array('stash -v 1', 'save'),
            array('stash --value="1"', 'save'),
            array('stash --value=\'1\'', 'save'),

            // options+args with default sub command
            array('stash -o arg', 'save'),
            array('stash --option arg', 'save'),
            array('stash -v1 arg', 'save'),
            array('stash -v 1 arg', 'save'),
            array('stash --value="1" arg', 'save'),
            array('stash --value=\'1\' arg', 'save'),

            // default option command
            array('server', 'list'),

            // options with default option command
            array('server -o', 'list'),
            array('server --option', 'list'),
            array('server -v1', 'list'),
            array('server -v 1', 'list'),
            array('server --value="1"', 'list'),
            array('server --value=\'1\'', 'list'),

            // options+args with default option command
            array('server -o arg', 'list'),
            array('server --option arg', 'list'),
            array('server -v1 arg', 'list'),
            array('server -v 1 arg', 'list'),
            array('server --value="1" arg', 'list'),
            array('server --value=\'1\' arg', 'list'),

            // multiple default sub commands
            array('bind', 'list'),

            // options with multiple default sub commands
            array('bind -o', 'list'),
            array('bind --option', 'list'),
            array('bind -v1', 'list'),
            array('bind -v 1', 'list'),
            array('bind --value="1"', 'list'),
            array('bind --value=\'1\'', 'list'),

            // options+args with multiple default sub commands
            array('bind -o arg binding', 'add'),
            array('bind --option arg binding', 'add'),
            array('bind -v1 arg binding', 'add'),
            array('bind -v 1 arg binding', 'add'),
            array('bind --value="1" arg binding', 'add'),
            array('bind --value=\'1\' arg binding', 'add'),
        );
    }

    public function testSuggestClosestAlternativeIfCommandNotFound()
    {
        try {
            $this->resolver->resolveCommand(new StringArgs('packa'), self::$application);
            $this->fail('Expected a CannotResolveCommandException');
        } catch (CannotResolveCommandException $e) {
            $this->assertRegExp('~Did you mean one of these\?\s+pack\s+package~', $e->getMessage());
        }

        try {
            $this->resolver->resolveCommand(new StringArgs('packag'), self::$application);
            $this->fail('Expected a CannotResolveCommandException');
        } catch (CannotResolveCommandException $e) {
            $this->assertRegExp('~Did you mean one of these\?\s+package\s+pack~', $e->getMessage());
        }
    }
}

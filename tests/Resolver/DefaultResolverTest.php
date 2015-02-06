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
use Symfony\Component\Console\Input\StringInput;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\Command\CommandCollection;
use Webmozart\Console\Api\Config\CommandConfig;
use Webmozart\Console\Api\Resolver\CommandNotDefinedException;
use Webmozart\Console\Resolver\DefaultResolver;

/**
 * @since  1.0
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class DefaultResolverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CommandCollection
     */
    private $commands;

    /**
     * @var DefaultResolver
     */
    private $resolver;

    protected function setUp()
    {
        $this->commands = new CommandCollection(array(
            new Command(
                CommandConfig::create('package')
                    ->addAlias('package-alias')
                    ->beginSubCommand('add')
                        ->addAlias('add-alias')
                    ->end()
                    ->beginSubCommand('addon')->end()
                    ->beginOptionCommand('delete', 'd')
                        ->addAlias('delete-alias')
                    ->end()
                    ->beginOptionCommand('delete-all')->end()
            ),
            new Command(CommandConfig::create('pack')),
            new Command(
                CommandConfig::create('default')
                    ->beginOptionCommand('do', 'D')->end()
            ),
            new Command(
                CommandConfig::create('stash')
                    ->beginSubCommand('save')
                        ->beginOptionCommand('do', 'D')->end()
                    ->end()
                    ->setDefaultSubCommand('save')
            ),
            new Command(
                CommandConfig::create('server')
                    ->beginOptionCommand('list')->end()
                    ->setDefaultOptionCommand('list')
            ),
        ));

        $this->resolver = new DefaultResolver('default');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfDefaultCommandNameNull()
    {
        new DefaultResolver(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfDefaultCommandNameEmpty()
    {
        new DefaultResolver('');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateFailsIfDefaultCommandNameNoString()
    {
        new DefaultResolver(1234);
    }

    /**
     * @dataProvider getInputOutputTests
     */
    public function testResolveCommand($inputString, $commandName)
    {
        $input = new StringInput($inputString);

        $command = $this->resolver->resolveCommand($input, $this->commands);

        $this->assertInstanceOf('Webmozart\Console\Api\Command\Command', $command);
        $this->assertSame($commandName, $command->getName());
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
            array('package --delete-alias', 'delete'),
            array('package --delete-alias arg', 'delete'),

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

            array('package --delete-alias -o', 'delete'),
            array('package --delete-alias --option', 'delete'),
            array('package --delete-alias -v1', 'delete'),
            array('package --delete-alias -v 1', 'delete'),
            array('package --delete-alias --value="1"', 'delete'),
            array('package --delete-alias --value=\'1\'', 'delete'),

            array('package --delete-alias -o arg', 'delete'),
            array('package --delete-alias --option arg', 'delete'),
            array('package --delete-alias -v1 arg', 'delete'),
            array('package --delete-alias -v 1 arg', 'delete'),
            array('package --delete-alias --value="1" arg', 'delete'),
            array('package --delete-alias --value=\'1\' arg', 'delete'),

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

            // options before long option command of default command
            array('-o --do', 'do'),
            array('--option --do', 'do'),
            array('-v1 --do', 'do'),
            array('-v 1 --do', 'do'),
            array('--value="1" --do', 'do'),
            array('--value=\'1\' --do', 'do'),

            // options before short option command of default command
            array('-o -D', 'do'),
            array('--option -D', 'do'),
            array('-v1 -D', 'do'),
            array('-v 1 -D', 'do'),
            array('--value="1" -D', 'do'),
            array('--value=\'1\' -D', 'do'),

            // options after long option command of default command
            array('--do -o', 'do'),
            array('--do --option', 'do'),
            array('--do -v1', 'do'),
            array('--do -v 1', 'do'),
            array('--do --value="1"', 'do'),
            array('--do --value=\'1\'', 'do'),

            // options after short option command of default command
            array('-D -o', 'do'),
            array('-D --option', 'do'),
            array('-D -v1', 'do'),
            array('-D -v 1', 'do'),
            array('-D --value="1"', 'do'),
            array('-D --value=\'1\'', 'do'),

            // options+args after long option command of default command
            array('--do -o arg', 'do'),
            array('--do --option arg', 'do'),
            array('--do -v1 arg', 'do'),
            array('--do -v 1 arg', 'do'),
            array('--do --value="1" arg', 'do'),
            array('--do --value=\'1\' arg', 'do'),

            // options+args after short option command of default command
            array('-D -o arg', 'do'),
            array('-D --option arg', 'do'),
            array('-D -v1 arg', 'do'),
            array('-D -v 1 arg', 'do'),
            array('-D --value="1" arg', 'do'),
            array('-D --value=\'1\' arg', 'do'),

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

            // options before long option command of default sub command
            array('stash -o --do', 'do'),
            array('stash --option --do', 'do'),
            array('stash -v1 --do', 'do'),
            array('stash -v 1 --do', 'do'),
            array('stash --value="1" --do', 'do'),
            array('stash --value=\'1\' --do', 'do'),

            // options before short option command of default sub command
            array('stash -o -D', 'do'),
            array('stash --option -D', 'do'),
            array('stash -v1 -D', 'do'),
            array('stash -v 1 -D', 'do'),
            array('stash --value="1" -D', 'do'),
            array('stash --value=\'1\' -D', 'do'),

            // options after long option command of default sub command
            array('stash --do -o', 'do'),
            array('stash --do --option', 'do'),
            array('stash --do -v1', 'do'),
            array('stash --do -v 1', 'do'),
            array('stash --do --value="1"', 'do'),
            array('stash --do --value=\'1\'', 'do'),

            // options after short option command of default sub command
            array('stash -D -o', 'do'),
            array('stash -D --option', 'do'),
            array('stash -D -v1', 'do'),
            array('stash -D -v 1', 'do'),
            array('stash -D --value="1"', 'do'),
            array('stash -D --value=\'1\'', 'do'),

            // options+args after long option command of default sub command
            array('stash --do -o arg', 'do'),
            array('stash --do --option arg', 'do'),
            array('stash --do -v1 arg', 'do'),
            array('stash --do -v 1 arg', 'do'),
            array('stash --do --value="1" arg', 'do'),
            array('stash --do --value=\'1\' arg', 'do'),

            // options+args after short option command of default sub command
            array('stash -D -o arg', 'do'),
            array('stash -D --option arg', 'do'),
            array('stash -D -v1 arg', 'do'),
            array('stash -D -v 1 arg', 'do'),
            array('stash -D --value="1" arg', 'do'),
            array('stash -D --value=\'1\' arg', 'do'),

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
        );
    }

    public function testSuggestClosestAlternativeIfCommandNotFound()
    {
        try {
            $this->resolver->resolveCommand(new StringInput('packa'), $this->commands);
            $this->fail('Expected a CommandNotDefinedException');
        } catch (CommandNotDefinedException $e) {
            $this->assertRegExp('~Did you mean one of these\?\s+pack\s+package~', $e->getMessage());
        }

        try {
            $this->resolver->resolveCommand(new StringInput('packag'), $this->commands);
            $this->fail('Expected a CommandNotDefinedException');
        } catch (CommandNotDefinedException $e) {
            $this->assertRegExp('~Did you mean one of these\?\s+package\s+pack~', $e->getMessage());
        }
    }
}

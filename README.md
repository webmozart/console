Webmozart Console
=================

[![Build Status](https://travis-ci.org/webmozart/console.svg?branch=master)](https://travis-ci.org/webmozart/console)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/webmozart/console/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/webmozart/console/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/4160f60e-541b-4090-a850-3005e84d6a44/mini.png)](https://insight.sensiolabs.com/projects/1ca0803e-6509-45b9-bd5b-b899d9680625)
[![Latest Stable Version](https://poser.pugx.org/webmozart/console/v/stable.svg)](https://packagist.org/packages/webmozart/console)
[![Total Downloads](https://poser.pugx.org/webmozart/console/downloads.svg)](https://packagist.org/packages/webmozart/console)
[![Dependency Status](https://www.versioneye.com/php/webmozart:console/1.0.0/badge.svg)](https://www.versioneye.com/php/webmozart:console/1.0.0)

Latest release: [1.0.0-beta](https://packagist.org/packages/webmozart/console#1.0.0-beta)

PHP >= 5.3.9

A usable, beautiful and easily testable console toolkit written in PHP.

Goals
-----

The goal of this package is:

* to build PHP applications similar to the "git" command
* with a minimum amount of code
* that are testable
* robust
* and beautiful.

None of the existing console libraries matched these requirements, so I
refactored the [Symfony Console component] into what you can see here.

Installation
------------

Use [Composer] to install the package:

```
$ composer require webmozart/console:~1.0@beta
```

Basic Configuration
-------------------

Console applications are configured via configuration classes. As example, we
will create the "git" command in PHP:

```php
use Webmozart\Console\Config\DefaultApplicationConfig;

class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            ->setName('git')
            ->setVersion('1.0.0')
            
            // ...
        ;
    }
}
```

This basic configuration tells the console that the executable is called "git" 
and that the current version is 1.0.0. Let's create the "git" executable now:

```php
#!/usr/bin/env php
<?php

use Webmozart\Console\ConsoleApplication;

if (file_exists($autoload = __DIR__.'/../../../autoload.php')) {
    require_once $autoload;
} else {
    require_once __DIR__.'/../vendor/autoload.php';
}

$cli = new ConsoleApplication(new GitApplicationConfig());
$cli->run();
```

The complicated autoload block makes sure that the autoload file is found both
when you run the executable directly in your package and when your package is
installed in another project via Composer.

Change the permissions of your executable and try to run it:

```
$ chmod a+x bin/git
$ bin/git
Git version 1.0.0
...
```

Commands
--------

So far, our application doesn't do much. Let's add a command "log" that displays
the latest commits:

```php
class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('log')
                ->setDescription('Show the latest commits')
                ->setHandler(new LogCommandHandler())
            ->endCommand()
        ;
    }
}
```

As you can see, the execution of the command is delegated to a 
`LogCommandHandler`. Since the handler is a separate class, it can easily be
tested in isolation. Let's implement a basic handler:

```php
use Webmozart\Console\Api\Args\Args;
use Webmozart\Console\Api\Command\Command;
use Webmozart\Console\Api\IO\IO;

class LogCommandHandler
{
    public function handle(Args $args, IO $io, Command $command)
    {
        // Simulate the retrieval of the commits
        $commits = array(
            'commit1',
            'commit2',
            'commit3',
        );
        
        foreach ($commits as $commit) {
            $io->writeLine($commit);
        }
        
        return 0;
    }
}
```

The `handle()` method of our command handler retrieves up to three arguments:

* `Args $args`: The arguments and options passed when calling the command.
* `IO $io`: The I/O, which gives access to the standard input, the standard
  output and the error output.
* `Command $command`: The currently executed command.

You can leave away the arguments that you don't need.

Every handler should return 0 if it was processed successfully and any
integer between 1 and 255 if it failed.

Let's run the command:

```
$ bin/git log
commit1
commit2
commit3
```

Arguments
---------

Next we'll add an argument `<branch>` to the "log" command with which we can
select the branch to display:

```php
use Webmozart\Console\Api\Args\Format\Argument;

class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('log')
                // ...
                
                ->addArgument('branch', Argument::OPTIONAL, 'The branch to display', 'master')
            ->endCommand()
        ;
    }
}
```

We added an optional argument "branch" with the default value "master". We can
access the value of the argument through the `Args` instance in the handler:

```php
class LogCommandHandler
{
    public function handle(Args $args, IO $io)
    {
        $io->writeLine('Branch: '.$args->getArgument('branch'));
        $io->writeLine('--');
        
        // ...
    }
}
```

Let's run the command with the argument:

```
$ bin/git log 1.0
Branch: 1.0
--
commit1
commit2
commit3
```
 
The second argument of the `addArgument()` method accepts a bitwise combination 
of different flags:

Constant                  | Description
------------------------- | ------------------------------------------------------
`Argument::OPTIONAL`      | The argument is optional. If you don't pass a default value, the default value is `null`.
`Argument::REQUIRED`      | The argument must be passed when calling the command.
`Argument::MULTI_VALUED`  | The argument may be passed multiple times. The command handler receives an array of all passed values.
`Argument::STRING`        | The argument is parsed as string (the default).
`Argument::BOOLEAN`       | The argument is parsed as boolean.
`Argument::INTEGER`       | The argument is parsed as integer.
`Argument::FLOAT`         | The argument is parsed as float.
`Argument::NULLABLE`      | Convert "null" to `null`.

Options
-------

Options are additional, optional settings that you can pass to your command.
Let's add the option `--max=<limit>` to our command which limits the number of 
displayed commits to the passed limit: 

```php
use Webmozart\Console\Api\Args\Format\Option;

class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('log')
                // ...
                
                ->addOption('max', null, Option::REQUIRED_VALUE, 'The maximum number of commits', 25)
            ->endCommand()
        ;
    }
}
```

The configuration of options is very similar to the configuration of arguments.
We created a `--max` option, which requires a value. If the option is not set
by the user, its value defaults to 25.

We can access the passed value with the `getOption()` method in our command
handler:

```php
class LogCommandHandler
{
    public function handle(Args $args, IO $io)
    {
        // ...
        
        $io->writeLine('Limit: '.$args->getOption('max').' commits');
        
        // ...
    }
}
```

Additionally, you will frequently need the `isOptionSet()` method, which tells
you whether the user actually passed the option when calling the command.

Let's run the command with the option:

```
$ bin/git log --max 10
Branch: master
Limit: 10 commits
--
commit1
commit2
commit3
```

Options support short names that consist of a single character only. Instead of
two leading dashes, short option names are only prefixed with one dash, for 
example: `-m`. Let's add this alias to our option by setting the second argument
of the `addOption()` method:

```php
use Webmozart\Console\Api\Args\Format\Option;

class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('log')
                // ...
                
                ->addOption('max', 'm', Option::REQUIRED_VALUE, 'The maximum number of commits', 25)
            ->endCommand()
        ;
    }
}
```

Now the command can also be run like this:

```
$ bin/git log -m 10
```

Like for arguments, options support a bitwise combination of different flags
that control how the option is processed:

Constant                      | Description
----------------------------- | ------------------------------------------------------
`Option::NO_VALUE`            | The option accepts no value. Used for simple on/off settings.
`Option::OPTIONAL_VALUE`      | The option accepts a value, but the value is option.
`Option::REQUIRED_VALUE`      | The option value needs to be set when passing the option.
`Option::MULTI_VALUED`        | The option may be passed multiple times. The command handler receives an array of all passed values.
`Option::STRING`              | The option is parsed as string (the default).
`Option::BOOLEAN`             | The option is parsed as boolean.
`Option::INTEGER`             | The option is parsed as integer.
`Option::FLOAT`               | The option is parsed as float.
`Option::NULLABLE`            | Convert "null" to `null`.
`Option::PREFER_LONG_NAME`    | The help lists the long form (`--max`) as suggested way of passing the option (the default). 
`Option::PREFER_SHORT_NAME`   | The help lists the short form (`-m`) as suggested way of passing the option.
 
Dependencies
------------

Very often, our command handlers rely on external services to access information
or execute business logic. These services can be injected through the 
constructor of the command handler. For example, assume that we need a
`CommitRepository` to access the commits listed in the "log" command: 

```php
class LogCommandHandler
{
    private $repository;
    
    public function __construct(CommitRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function handle(Args $args, IO $io)
    {
        $commits = $this->repository->findByBranch($args->getArgument('branch'));
        
        // ...
    }
}
```

Since the `CommitRepository` is injected into the command handler, we can
easily replace the repository with a mock object when we test the handler.

We also need to change the configuration to inject the repository:

```php
class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('log')
                // ...
                
                ->setHandler(new LogCommandHandler(new CommitRepository()))
            ->endCommand()
        ;
    }
}
```

If your application grows, a lot of objects will be created whenever the
`configure()` method is executed - even if the commands that need these objects
are not executed. Let's change our call to `setHandler()` to a closure so that
the handler is executed on demand:

```php
class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('log')
                // ...
                
                ->setHandler(function () {
                    return new LogCommandHandler(new CommitRepository());
                })
            ->endCommand()
        ;
    }
}
```

Now, the `LogCommandHandler` and its dependencies are only created when "log"
command is executed.

Sub Commands
------------

The "log" command was a very simple example, but many real-world use cases are
more complicated than that. Consider the "git remote" command, which is split
into several sub-commands:

```
$ git remote
$ git remote add ...
$ git remote rename ...
$ git remote remove ...
```

Such sub-commands can be introduced with the `beginSubCommand()` method in the
configuration:

```php
class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('remote')
                ->setDescription('Manage the remotes of your Git repository')
                ->setHandler(function () {
                    return new RemoteCommandHandler(new RemoteManager());
                })
                
                ->beginSubCommand('list')
                    ->setHandlerMethod('handleList')
                ->endSubCommand()
                
                ->beginSubCommand('add')
                    ->setHandlerMethod('handleAdd')
                    ->addArgument('name', Argument::REQUIRED, 'The remote name')
                    ->addArgument('url', Argument::REQUIRED, 'The remote URL')
                ->endSubCommand()
                
                // ...
            ->endCommand()
        ;
    }
}
```

Like regular commands, sub-commands accept options, arguments and command
handlers. However, instead of creating *one command handler per sub-command*, it
is often more convenient to create a single handler with *one method per
sub-command*. The handler method can be selected with `setHandlerMethod()`.

The basic implementation of our `RemoteCommandHandler` is very similar to the
command handler of the "log" command:

```php
class RemoteCommandHandler
{
    private $manager;
    
    public function __construct(RemoteManager $manager)
    {
        $this->manager = $manager;
    }
    
    public function handleList(Args $args, IO $io)
    {
        $remotes = $this->manager->getRemotes();
        
        // ...
        
        return 0;
    }
    
    public function handleAdd(Args $args)
    {
        $name = $args->getArgument('name');
        $url = $args->getArgument('url');
        
        $this->manager->addRemote($name, $url);
        
        return 0;
    }
}
```

If we inject a working `RemoteManager`, we can execute both commands now:

```
$ git remote add origin http://example.com
$ git remote list
```

If we want to execute the "list" command by default if no sub-command is
selected, we need to mark it as default command with `markDefault()`:

```php
class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->beginCommand('remote')
                // ...
                
                ->beginSubCommand('list')
                    // ...
                    
                    ->markDefault()
                ->endSubCommand()
                
                // ...
            ->endCommand()
        ;
    }
}
```

Now we can run the list command with:

```
$ git remote
```

At last, you can use `markAnonymous()` if you want to run the command *just
with* `git remote`, *but not* with `git remote list`.

Colors and Styles
-----------------

The console supports colors and styles in the output that you pass to the
`IO` class. Let's output some bold text for example:

```
$io->writeLine("Current branch: <b>$branch</b>");
```

Just like in HTML, the styles are inserted in your text using markup tags. The
following styles are defined by default:

Tag       | Description
--------- | ------------------------------------
`<b>`     | Bold text
`<u>`     | Underlined text
`<bu>`    | Bold and underlined text
`<c1>`    | Colored text (default: cyan)
`<c2>`    | Colored text (default: yellow)
`<warn>`  | Black text with a yellow background
`<error>` | White text with a red background

You can change the existing styles or add custom styles using the `addStyle()`
method in your configuration:

```php
use Webmozart\Console\Api\Formatter\Style;

class GitApplicationConfig extends DefaultApplicationConfig
{
    protected function configure()
    {
        $this
            // ...
            
            ->addStyle(Style::tag('c3')->fgMagenta())
        ;
    }
}
```

Here is a list of methods that can be called on the `Style` class:

Method         | Description
-------------- | -------------------------------------------
`bold()`       | Bold text
`underlined()` | Underlined text
`blinking()`   | Blinking text
`inverse()`    | Invert the foreground and background color
`hidden()`     | Hidden text
`fgDefault()`  | Use the system's default text color
`fgBlack()`    | Black text
`fgRed()`      | Red text
`fgGreen()`    | Green text
`fgYellow()`   | Yellow text
`fgBlue()`     | Blue text
`fgMagenta()`  | Magenta text
`fgCyan()`     | Cyan text
`fgWhite()`    | White text
`bgDefault()`  | Use the system's default background color
`bgBlack()`    | Black background
`bgRed()`      | Red background
`bgGreen()`    | Green background
`bgYellow()`   | Yellow background
`bgBlue()`     | Blue background
`bgMagenta()`  | Magenta background
`bgCyan()`     | Cyan background
`bgWhite()`    | White background

Tables
------

You can draw tables with the `Table` class:

```php
use Webmozart\Console\UI\Component\Table;

$table = new Table();

$table->setHeaderRow(array('Remote Name', 'Remote URL'));

foreach ($remotes as $remote) {
    $table->addRow(array($remote->getName(), $remote->getUrl()));
}

$table->render($io);
```

Tables support word wrapping, so that overlong text does not break the output.
The default output of the above table is similar to this:

```
+-------------+-------------------------------+
| Remote Name | Remote URL                    |
+-------------+-------------------------------+
| origin      | http://example.com/repository |
| fork        | http://fork.com/repository    |
+-------------+-------------------------------+
```

You can change the style of the table by passing a custom `TableStyle` to the
constructor. Either build your own `TableStyle` or use one of the predefined
ones:

```php
use Webmozart\Console\UI\Component\Table;
use Webmozart\Console\UI\Component\TableStyle;

$table = new Table(TableStyle::solidBorder());
```

```
┌──────────────┬───────────────────────────────┐
│ Remote Name  │ Remote URL                    │
├──────────────┼───────────────────────────────┤
│ origin       │ http://example.com/repository │
│ fork         │ http://fork.com/repository    │
└──────────────┴───────────────────────────────┘
```

The following predefined table styles exist:

Style                       | Description
--------------------------- | --------------------------------------------------
`TableStyle::asciiBorder()` | Uses ASCII characters for the border (the default)
`TableStyle::solidBorder()` | Uses Unicode characters for the border
`TableStyle::borderless()`  | No border

Authors
-------

* [Bernhard Schussek] a.k.a. [@webmozart]
* [The Community Contributors]

Contribute
----------

Contributions to are very welcome!

* Report any bugs or issues you find on the [issue tracker].
* You can grab the source code at Puli’s [Git repository].

Support
-------

If you are having problems, send a mail to bschussek@gmail.com or shout out to
[@webmozart] on Twitter.

License
-------

All contents of this package are licensed under the [MIT license].

[Composer]: https://getcomposer.org/
[Symfony Console component]: http://symfony.com/doc/current/components/console/introduction.html
[Bernhard Schussek]: http://webmozarts.com
[The Community Contributors]: https://github.com/webmozart/console/graphs/contributors
[issue tracker]: https://github.com/webmozart/console/issues
[Git repository]: https://github.com/webmozart/console
[@webmozart]: https://twitter.com/webmozart
[MIT license]: LICENSE

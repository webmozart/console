<?php

use Webmozart\Console\Config\DefaultApplicationConfig;
use Webmozart\Console\ConsoleApplication;
use Webmozart\Console\Handler\CallbackHandler;

require_once __DIR__.'/../../vendor/autoload.php';

$config = DefaultApplicationConfig::create()
    ->setTerminateAfterRun(true)

    ->editCommand('help')
        ->setHandler(new CallbackHandler(function () {
            return 123;
        }))
    ->end()
;

$application = new ConsoleApplication($config);
$application->run();

// Should not be executed
exit(234);

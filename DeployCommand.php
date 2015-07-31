<?php

require_once 'vendor/autoload.php';

FileSystemCache::$cacheDir = __DIR__ . '/data/';

$console = new ConsoleKit\Console();
$console->addCommand('oat\deploymentsTools\AddCommand'); 
$console->addCommand('oat\deploymentsTools\CleanStackCommand'); 
$console->addCommand('oat\deploymentsTools\DebugCommand'); 
$console->addCommand('oat\deploymentsTools\ExecCommand'); 
$console->run();

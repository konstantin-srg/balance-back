<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ERROR/* | E_WARNING */| E_PARSE | E_COMPILE_ERROR  | E_COMPILE_WARNING | E_CORE_ERROR | E_RECOVERABLE_ERROR);

define('DIST_DIR', realpath(realpath(__DIR__)));
define('BASE_DIR', realpath(realpath(__DIR__ . '/..')));

const DEV = true;
//define('MODULES_DIR', realpath(realpath(__DIR__ . '/..')));
$composer = include BASE_DIR . '/vendor/autoload.php';

use Pipe\Debug;
Debug\Dumper\Dumper::getInstance()->start();
/*Debug\Profiler\Profiler::getInstance()
	->start()
	->addStep('engine');*/

$whoops = new \Whoops\Run();

$handler = new \Whoops\Handler\PrettyPageHandler();
$handler->setEditor('phpstorm');

$whoops->pushHandler($handler);
$whoops->register();
/*
$handler->addDataTable('Ice-cream I like', [
	'Chocolate' => 'yes',
	'Coffee & chocolate' => 'a lot',
	'Strawberry & chocolate' => 'it\'s alright',
	'Vanilla' => 'ew',
]);*/
use Pipe\Cache;
use Pipe\Debug\Timer;
use Pipe\Mvc\Engine\Engine;

Engine::getInstance()
	->setConfig([
		'composer'  => $composer,
		'baseDir'   => BASE_DIR,
	])
	->start();

Debug\Profiler\Profiler::getInstance()->finish();


<?php /** @noinspection PhpUnhandledExceptionInspection */

use JakubBoucek\DebugEnabler\DebugEnabler;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(DebugEnabler::isDebugByEnv());
$configurator->enableTracy(__DIR__ . '/../log', 'pan@jakubboucek.cz');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/../local/config.local.neon');

$container = $configurator->createContainer();

return $container;

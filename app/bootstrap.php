<?php /** @noinspection PhpUnhandledExceptionInspection */

use JakubBoucek\DebugEnabler\DebugEnabler;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(DebugEnabler::isDebugByEnv());
$configurator->enableTracy(__DIR__ . '/../log', 'b3z2g3u8a0j7f4x2@techheaven.slack.com');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/../local/config.local.neon');

$container = $configurator->createContainer();

return $container;

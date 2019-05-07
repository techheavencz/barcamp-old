<?php
declare(strict_types=1);

/** @noinspection PhpUnhandledExceptionInspection */

namespace App;

use JakubBoucek\DebugEnabler\DebugEnabler;
use Nette\Configurator;


class Bootstrap
{
    public static function boot(): Configurator
    {


        $configurator = new Configurator;

        $configurator->setDebugMode(DebugEnabler::isDebugByEnv());
        $configurator->enableTracy(__DIR__ . '/../log', 'pan@jakubboucek.cz');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/../temp');

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        $configurator->addConfig(__DIR__ . '/config/config.neon');
        $configurator->addConfig(__DIR__ . '/../local/config.local.neon');


        return $configurator;
    }
}

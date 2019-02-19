<?php

namespace App;

use Nette;
use Nette\Application\IRouter;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;


    /**
     * @return IRouter
     * @throws Nette\InvalidArgumentException
     */
	public static function createRouter(): IRouter
    {
		$router = new RouteList;
        $router[] = new Route('2014[/<path .+>]', 'Archive:2014');
        $router[] = new Route('2015[/<path .+>]', 'Archive:2015');
        $router[] = new Route('2016[/<path .+>]', 'Archive:2016');
        $router[] = new Route('2017[/<path .+>]', 'Archive:2017');
        $router[] = new Route('2018[/<path .+>]', 'Archive:2018');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}
}

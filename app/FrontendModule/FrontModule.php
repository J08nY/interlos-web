<?php

class FrontModule {

	public static function createRouter($prefix) {
		$routeList = new \Nette\Application\Routers\RouteList($prefix);
		$routeList[] = new \Nette\Application\Routers\Route('<presenter>/<action>', 'Default:default');
		return $routeList;
	}

	public static function getModuleDir() {
		return APP_DIR . "/FrontendModule";
	}

}

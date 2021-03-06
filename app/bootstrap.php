<?php
# In a case of emergency uncomment one of following lines
# include_once __DIR__ . '/templates/.maintenance.phtml';

# Use this when problem in time of contest occures
use Nette\Application\Routers\Route;
use Nette\DI\Container;
use Nette\Http\Request;

$problem = FALSE; // Set to true and add whitelisted IPs
$remoteIP = $_SERVER['REMOTE_ADDR'];
$allowedIP = array("127.0.0.0", "::1");
if ($problem && !in_array($remoteIP, $allowedIP)) {
	include_once __DIR__ . '/templates/.problem.phtml';
}

// Load Nette Framework or autoloader generated by Composer
require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator();

// Enable Nette Debugger for error visualisation & logging
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->addDirectory(__DIR__ . '/../vendor/others')
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon', $configurator::NONE);
// Tuning config with local only settings like passwords etc.
if (file_exists(__DIR__ . '/config/config.local.neon')) {
	$configurator->addConfig(__DIR__ . '/config/config.local.neon', $configurator::NONE);
}

// Add DIBI extension for database connection
$configurator->onCompile[] = function ($configurator, $compiler) {
	$compiler->addExtension('dibi', new \Dibi\Bridges\Nette\DibiExtension22());
};

/** @var Container $container */
$container = $configurator->createContainer();

// Turn on HTTPS when on proper domain
$securedDomains = $container->parameters['securedDomains'];
/** @var Request $httpRequest */
$httpRequest = $container->getByType('Nette\\Http\\Request');
if (in_array($httpRequest->getUrl()->getHost(), $securedDomains, TRUE)) {
	Route::$defaultFlags = Route::SECURED;
}

// Setup router
$container->router[] = FrontModule::createRouter("Front");

// Create directory for session data (Nette does not seem to create it on its own, then fails to initialize session)
@mkdir($container->session->options['save_path'], 0777, true);

Interlos::injectContainer($container);

return $container;

<?php

declare(strict_types=1);

use Core\Facade\Config;
use Core\Handlers\HttpErrorHandler;
use Core\Handlers\ShutdownHandler;
use Core\ResponseEmitter\ResponseEmitter;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

const BASE_PATH = __DIR__.'/../';
require BASE_PATH.'vendor/autoload.php';
require BASE_PATH.'helpers/functions.php';

$container = new ContainerBuilder;

$bootstrap = require base_path('app/bootstrap.php');
$bootstrap($container);

$container = $container->build();

AppFactory::setContainer($container);
$app = AppFactory::create();
$responseFactory = $app->getResponseFactory();
$callableResolver = $app->getCallableResolver();

$middleware = require base_path('app/middleware.php');
$middleware($app);

$routes = require base_path('app/routes.php');
$routes($app);

$displayErrorDetails = (bool) Config::get('middleware.displayErrorDetails');
$logErrors = (bool) Config::get('middleware.logErrors');
$logErrorDetails = (bool) Config::get('middleware.logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add built-in middleware
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter;
$responseEmitter->emit($response);

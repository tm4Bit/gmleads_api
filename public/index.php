<?php

declare(strict_types=1);

use Core\Exception\HttpException;
use Http\Controller\GenerateEventController;
use Http\Controller\HealthController;
use Http\Controller\StoreLeadsController;
use Http\Middleware\ContentTypeHeadersMiddleware;
use Http\Middleware\Cors;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;

const BASE_PATH = __DIR__.'/../';
require BASE_PATH.'vendor/autoload.php';
require BASE_PATH.'helpers/functions.php';
require base_path('app/bootstrap.php');

$app = AppFactory::create();
$responseFactory = $app->getResponseFactory();

$app->addBodyParsingMiddleware();
$app->add(new Cors($responseFactory));
$app->add(new ContentTypeHeadersMiddleware);
$app->addRoutingMiddleware();

$errorHandler = function (Request $request, Throwable $exception) use ($app) {
    if ($exception instanceof HttpException) {
        $statusCode = $exception->getHttpStatusCode();
    } elseif ($exception instanceof PDOException) {
        $statusCode = 500;
    } else {
        $statusCode = 500;
    }

    $payload = [
        'error' => $exception->getMessage(),
    ];
    if (ini_get('display_errors') === '1') { // Add more detail if display_errors is on
        $payload['exception_type'] = get_class($exception);
        $payload['trace'] = $exception->getTraceAsString(); // Be careful with exposing traces in prod
    }
    $response = $app->getResponseFactory()->createResponse();
    $response
        ->getBody()
        ->write(json_encode($payload, JSON_UNESCAPED_UNICODE));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($statusCode);
};

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->group('/api', function (RouteCollectorProxy $group) {
    $group->get('/up', [HealthController::class, 'handle']);
    $group->post('/generate-event/{tableName:[a-zA-Z0-9_]+}', [GenerateEventController::class, 'handle']);
    $group->post('/leads', [StoreLeadsController::class, 'handle']);
});

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

$app->run();

<?php

declare(strict_types=1);

use Http\Controller\GenerateEventController;
use Http\Controller\HealthController;
use Http\Controller\SendLeadsController;
use Http\Controller\StoreLeadsController;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->options('/{routes:.+}', function ($request, $response) {
        // Handle preflight requests for CORS
        return $response;
    });

    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/up', [HealthController::class, 'handle']);
        $group->post('/generate-event/{tableName:[a-zA-Z0-9_]+}', [GenerateEventController::class, 'handle']);
        $group->post('/leads', [StoreLeadsController::class, 'handle']);
        $group->get('/crm/{tableName:[a-zA-Z0-9_]+}', [SendLeadsController::class, 'handle']);
    });

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request, 'No route found!');
    });

};

<?php

declare(strict_types=1);

use Ovlk\GMLeads\Events\Http\Controller\GenerateEventController;
use Ovlk\GMLeads\HealthCheck\HealthCheckController;
use Ovlk\GMLeads\Leads\Http\Controller\SendLeadsController;
use Ovlk\GMLeads\Leads\Http\Controller\StoreLeadsController;
use Slim\Exception\HttpNotFoundException;
use Slim\Routing\RouteCollectorProxy;

return function ($app) {
    $app->options('/{routes:.+}', function ($request, $response) {
        // Handle preflight requests for CORS
        return $response;
    });

    $app->group('/api', function (RouteCollectorProxy $group) {
        $group->get('/up', HealthCheckController::class);
        $group->post('/generate-event/{tableName:[a-zA-Z0-9_]+}', GenerateEventController::class);
        $group->post('/leads', StoreLeadsController::class);
        $group->get('/crm/{tableName:[a-zA-Z0-9_]+}', SendLeadsController::class);
    });

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
        throw new HttpNotFoundException($request, 'No route found!');
    });

};

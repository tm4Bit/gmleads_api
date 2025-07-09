<?php

declare(strict_types=1);

use Core\App;
use Core\Container;
use Core\Database;
use Core\LoggerFactory;
use GuzzleHttp\Client as HttpClient;
use Ovlk\GMLeads\UseCase\GenerateEventUseCase;
use Ovlk\GMLeads\UseCase\SendLeadsUseCase;
use Ovlk\GMLeads\UseCase\StoreLeadsUseCase;
use Psr\Log\LoggerInterface;

$container = new Container;

$container->bind('Core\Database', function () {
    $db = config('database', 'db');
    $username = config('database', 'username');
    $password = config('database', 'password');

    return new Database($db, $username, $password);
});

$container->bind(LoggerInterface::class, function () {
    return LoggerFactory::create();
});

$container->bind(HttpClient::class, function () {
    $base_uri = config('crm', 'endpoint');

    return new HttpClient([
        'base_uri' => $base_uri,
        'timeout' => 10.0,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
    ]);
});

$container->bind(GenerateEventUseCase::class, function () {
    $db = App::resolve(Database::class);

    return new GenerateEventUseCase($db);
});

$container->bind(StoreLeadsUseCase::class, function () {
    $db = App::resolve(Database::class);

    return new StoreLeadsUseCase($db);
});

$container->bind(SendLeadsUseCase::class, function () {
    $db = App::resolve(Database::class);
    $httpClient = App::resolve(HttpClient::class);
    $logger = App::resolve(LoggerInterface::class);

    return new SendLeadsUseCase($httpClient, $db, $logger);
});

App::setContainer($container);

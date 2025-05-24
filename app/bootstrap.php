<?php

declare(strict_types=1);

use Core\App;
use Core\Container;
use Core\Database;
use Ovlk\GMLeads\UseCase\GenerateEventUseCase;
use Ovlk\GMLeads\UseCase\StoreLeadsUseCase;

$container = new Container;

$container->bind('Core\Database', function () {
    $db = config('database', 'db');
    $username = config('database', 'username');
    $password = config('database', 'password');

    return new Database($db, $username, $password);
});

$container->bind('Ovlk\GMLeads\UseCase\GenerateEventUseCase', function () {
    $db = App::resolve(Database::class);

    return new GenerateEventUseCase($db);
});

$container->bind('Ovlk\GMLeads\UseCase\StoreLeadsUseCase', function () {
    $db = App::resolve(Database::class);

    return new StoreLeadsUseCase($db);
});

App::setContainer($container);

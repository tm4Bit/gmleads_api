<?php

declare(strict_types=1);

use Core\Facade\Config;
use Core\Facade\LoggerFactory;
use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $container) {
    $container->addDefinitions([
        LoggerInterface::class => fn () => LoggerFactory::create(),
        HttpClient::class => function () {
            $base_uri = Config::get('crm.endpoint');

            return new HttpClient([
                'base_uri' => $base_uri,
                'timeout' => 10.0,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ]);
        },
    ]);

    return $container;
};

<?php

namespace Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request as SlimRequest;
use Slim\Psr7\Uri;

abstract class TestCase extends BaseTestCase
{
    protected App $app;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->createApp();
    }

    protected function createApp(): App
    {
        if (! defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__).'/');
        }

        require_once BASE_PATH.'helpers/functions.php';

        $containerBuilder = new ContainerBuilder;

        $bootstrap = require BASE_PATH.'app/bootstrap.php';
        $bootstrap($containerBuilder);

        $container = $containerBuilder->build();
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        $middleware = require BASE_PATH.'app/middleware.php';
        $middleware($app);

        $routes = require BASE_PATH.'app/routes.php';
        $routes($app);

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();

        return $app;
    }

    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['HTTP_ACCEPT' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): Request {
        $uri = new Uri('', '', 80, $path);
        $handle = fopen('php://temp', 'w+');
        $stream = (new StreamFactory)->createStreamFromResource($handle);

        $h = new Headers;
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }

        return new SlimRequest($method, $uri, $h, $cookies, $serverParams, $stream);
    }
}

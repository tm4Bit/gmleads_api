<?php

namespace Core\Facade;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    /**
     * Create a Logger instance based on the configuration.
     */
    public static function create(): Logger
    {
        $config = Config::get('logger');

        $logger = new Logger($config['name']);
        $logger->pushHandler(new StreamHandler($config['path'], $config['level']));

        return $logger;
    }
}

<?php

namespace Core;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    public static function create(): Logger
    {
        $config = config('logger', null);

        $logger = new Logger($config['name']);
        $logger->pushHandler(new StreamHandler($config['path'], $config['level']));

        return $logger;
    }
}

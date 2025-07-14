<?php

return [
    'name' => 'app',
    'path' => (getenv('DOCKER')) ? 'php://stdout' : base_path('logs/app.log'),
    'level' => Monolog\Logger::DEBUG,
];

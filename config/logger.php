<?php

return [
    'name' => 'app',
    'path' => isset($_ENV['docker']) ? 'php://stdout' : base_path('logs/app.log'),
    'level' => Monolog\Logger::DEBUG,
];

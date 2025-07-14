<?php

use Core\Facade\Config;

const BASE_PATH = __DIR__.'/../';
require BASE_PATH.'vendor/autoload.php';
require BASE_PATH.'helpers/functions.php';
require base_path('app/bootstrap.php');

echo '<h1>Hello, GM Lead API!</h1>';
echo '<p>This is a playground for testing PHP code</p>';

$value = Config::get('middleware');

dd($value);

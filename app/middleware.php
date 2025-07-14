<?php

declare(strict_types=1);

use Http\Middleware\ContentTypeHeadersMiddleware;
// use Http\Middleware\Cors;
use Slim\App;

return function (App $app) {
    // $app->add(new Cors($responseFactory));
    $app->add(new ContentTypeHeadersMiddleware);
};

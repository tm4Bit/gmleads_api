<?php

declare(strict_types=1);

namespace Http\Controller;

use Core\App;
use Ovlk\GMLeads\UseCase\GenerateEventUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class GenerateEventController
{
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $tableName = $args['tableName'];

        $generateEventUseCase = App::resolve(GenerateEventUseCase::class);
        $result = $generateEventUseCase->execute($tableName);

        $response
            ->getBody()
            ->write(json_encode($result));

        return $response;
    }
}

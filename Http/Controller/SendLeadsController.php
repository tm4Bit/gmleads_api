<?php

declare(strict_types=1);

namespace Http\Controller;

use Core\App;
use Ovlk\GMLeads\UseCase\SendLeadsUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SendLeadsController
{
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $tableName = $args['tableName'];
        $sendLeadsUseCase = App::resolve(SendLeadsUseCase::class);
        $result = $sendLeadsUseCase->execute($tableName);
        $response->getBody()->write(json_encode($result));

        return $response->withStatus(200);
    }
}

<?php

declare(strict_types=1);

namespace Http\Controller;

use Core\App;
use Ovlk\GMLeads\UseCase\StoreLeadsUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class StoreLeadsController
{
    public function handle(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $storeLeadsUseCase = App::resolve(StoreLeadsUseCase::class);
        $storeLeadsUseCase->execute($body);

        return $response->withStatus(204);

    }
}

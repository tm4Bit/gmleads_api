<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Http\Controller;

use Core\Http\Controller\Controller;
use Ovlk\GMLeads\Events\UseCase\StoreLeadsUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class StoreLeadsController extends Controller
{
    public function __construct(LoggerInterface $logger, private StoreLeadsUseCase $storeLeadsUseCase)
    {
        parent::__construct($logger);
    }

    public function handle(): Response
    {
        $body = $this->request->getParsedBody();
        $this->storeLeadsUseCase->execute($body);

        return $this->jsonResponse(null, 204);

    }
}

<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Http\Controller;

use Core\Http\Controller\Controller;
use Ovlk\GMLeads\Events\UseCase\GenerateEventUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class GenerateEventController extends Controller
{
    public function __construct(LoggerInterface $logger, private GenerateEventUseCase $generateEventUseCase)
    {
        parent::__construct($logger);
    }

    public function handle(): Response
    {
        $tableName = $this->getParams('tableName');
        $result = $this->generateEventUseCase->execute($tableName);
        $response = $this->jsonResponse($result);

        return $response;
    }
}

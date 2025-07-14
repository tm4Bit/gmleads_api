<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Http\Controller;

use Core\Http\Controller\Controller;
use Ovlk\GMLeads\Events\UseCase\SendLeadsUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class SendLeadsController extends Controller
{
    public function __construct(LoggerInterface $logger, private SendLeadsUseCase $sendLeadsUseCase)
    {
        parent::__construct($logger);
    }

    public function handle(): Response
    {
        $tableName = $this->getParams('tableName');
        $result = $this->sendLeadsUseCase->execute($tableName);
        $response = $this->jsonResponse($result);

        return $response;
    }
}

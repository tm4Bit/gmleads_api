<?php

declare(strict_types=1);

namespace Ovlk\GMLeads;

use Core\Http\Controller\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class HealthCheckController extends Controller
{
    public function __construct(LoggerInterface $logger, private HealthCheckUseCase $healthCheckUseCase)
    {
        parent::__construct($logger);
    }

    public function handle(): ResponseInterface
    {
        $fuso = $this->healthCheckUseCase->execute();

        $this->logger->info('Verificação de saúde bem-sucedida.', [$fuso]);

        return $this->jsonResponse([
            'status' => 'ON',
            'message' => 'Serviço ativo',
        ]);
    }
}

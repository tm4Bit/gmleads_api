<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\HealthCheck;

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
        $timezone = $this->healthCheckUseCase->execute();

        $this->logger->info('Verificação de saúde bem-sucedida.', [$timezone]);

        return $this->jsonResponse([
            'status' => 'ON',
            'message' => 'Serviço ativo',
            'timezone' => $timezone,
        ]);
    }
}

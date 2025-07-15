<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\HealthCheck;

use Core\Exception\HttpException;
use Psr\Log\LoggerInterface;

class HealthCheckUseCase
{
    public function __construct(private HealthCheckRepositoryInterface $healthCheckRepository, private LoggerInterface $logger) {}

    public function execute()
    {
        try {
            $timezone = $this->healthCheckRepository->getDatabaseTimeZone();

            return $timezone;
        } catch (\Exception $e) {
            $this->logger->error('Erro ao conectar ao banco de dados.', ['error' => $e->getMessage()]);
            throw new HttpException('Erro ao conectar ao banco de dados: '.$e->getMessage());
        }
    }
}

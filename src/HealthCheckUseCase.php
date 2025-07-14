<?php

declare(strict_types=1);

namespace Ovlk\GMLeads;

use Core\Database;
use Core\Exception\HttpException;
use Psr\Log\LoggerInterface;

class HealthCheckUseCase
{
    public function __construct(private Database $db, private LoggerInterface $logger) {}

    public function execute()
    {
        try {
            return $this->db->queryBuilder('SELECT fuso FROM c_paises WHERE id = 1')->find();
        } catch (\Exception $e) {
            $this->logger->error('Erro ao conectar ao banco de dados.', ['error' => $e->getMessage()]);
            throw new HttpException('Erro ao conectar ao banco de dados: '.$e->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Domain\Repository;

use Core\Repository\Repository; // Mude o namespace se você criou a pasta.
use Ovlk\GMLeads\Leads\Repository\LeadRepositoryInterface;

class LeadRepository extends Repository implements LeadRepositoryInterface
{
    // O construtor é herdado, então não precisamos redeclará-lo
    // a menos que queiramos adicionar mais dependências.

    public function findLastSentLeadId(int $eventId): int
    {
        $stmt = $this->query(
            'SELECT evento_stop FROM crm WHERE evento_id = :evento_id ORDER BY mom DESC LIMIT 1',
            ['evento_id' => $eventId]
        );
        $result = $stmt->fetch();

        return $result ? (int) $result['evento_stop'] : 0;
    }

    public function findUnsentLeads(string $tableName, int $lastSentId): array
    {
        // O método query retorna o PDOStatement
        $stmt = $this->query(
            "SELECT * FROM `$tableName` WHERE id > :lastSentId ORDER BY id ASC",
            ['lastSentId' => $lastSentId]
        );
        // Agora você chama o fetchAll() no statement retornado
        return $stmt->fetchAll();
    }

    public function save(string $tableName, array $leadData): void
    {
        $insertColumns = array_keys($leadData);
        $placeholders = array_map(fn ($col) => ":$col", $insertColumns);
        $columnsSql = '`' . implode('`, `', $insertColumns) . '`';
        $placeholdersSql = implode(', ', $placeholders);

        $sql = "INSERT INTO `$tableName` ({$columnsSql}) VALUES ({$placeholdersSql})";
        
        $this->query($sql, array_combine($placeholders, array_values($leadData)));
    }

    public function logCrmSubmission(int $eventId, int $lastLeadId, string $responseMessage): void
    {
        $sql = 'INSERT INTO crm (evento_id, evento_stop, `return`, mom) VALUES (:evento_id, :evento_stop, :return, NOW())';
        $params = [
            'evento_id' => $eventId,
            'evento_stop' => $lastLeadId,
            'return' => $responseMessage,
        ];
        
        $this->query($sql, $params);
    }
}
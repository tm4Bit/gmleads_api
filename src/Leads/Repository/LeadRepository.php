<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Repository;

use Core\Database\Repository\Repository;

class LeadRepository extends Repository implements LeadRepositoryInterface
{
    public function findLastSentLeadId(int $eventId): int
    {
        $sql = 'SELECT evento_stop FROM crm WHERE evento_id = :evento_id ORDER BY mom DESC LIMIT 1';
        $stmt = $this->query($sql, ['evento_id' => $eventId]);
        $result = $stmt->fetch();

        return $result ? (int) $result['evento_stop'] : 0;
    }

    public function findUnsentLeads(string $tableName, int $lastSentId): array
    {
        $sql = "SELECT * FROM `$tableName` WHERE id > :lastSentId ORDER BY id ASC";
        $stmt = $this->query($sql, ['lastSentId' => $lastSentId]);

        return $stmt->fetchAll();
    }

    public function save(string $tableName, array $leadData): void
    {
        $insertColumns = array_keys($leadData);
        $placeholders = array_map(fn ($col) => ":$col", $insertColumns);
        $columnsSql = '`'.implode('`, `', $insertColumns).'`';
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

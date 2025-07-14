<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Repository;

use Core\Database;
use Core\Repository\Repository;

class EventRepository extends Repository implements EventRepositoryInterface
{
    public function __construct(private Database $db) {}

    public function findByTableName(string $tableName): ?array
    {
        $result = $this
            ->query('SELECT * FROM briefing WHERE tbl_clientes = :tableName', ['tableName' => $tableName])
            ->fetch();

        return $result ?: null;
    }

    public function findById(int $briefingId): ?array
    {
        $result = $this->query('SELECT id, tbl_clientes, pais AS briefing_pais_id FROM briefing WHERE id = :briefingId', [
                ':briefingId' => $briefingId,
            ])
            ->fetch();
        
        return $result ?: null;
    }

    public function findCountryInfo(int $countryId): ?array
    {
        $result = $this->query('SELECT * FROM c_paises WHERE id = :countryId', ['countryId' => $countryId])
            ->fetch();

        return $result ?: null;
    }

    public function getTableColumns(string $tableName): array
    {
        return $this->query("SHOW FULL COLUMNS FROM `{$tableName}`")
            ->fetchAll();
    }
}
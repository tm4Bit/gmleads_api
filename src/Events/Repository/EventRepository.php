<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Repository;

use Core\Database\Repository\Repository;

class EventRepository extends Repository implements EventRepositoryInterface
{
    public function findByTableName(string $tableName): ?array
    {
        $sql = 'SELECT * FROM briefing WHERE tbl_clientes = :tableName';
        $result = $this
            ->query($sql, ['tableName' => $tableName])
            ->fetch();

        return $result ?: null;
    }

    public function findById(int $briefingId): ?array
    {
        $sql = 'SELECT id, tbl_clientes, pais AS briefing_pais_id FROM briefing WHERE id = :briefingId';
        $result = $this
            ->query($sql, [':briefingId' => $briefingId])
            ->fetch();

        return $result ?: null;
    }

    public function findCountryInfo(int $countryId): ?array
    {
        $sql = 'SELECT * FROM c_paises WHERE id = :countryId';
        $result = $this
            ->query($sql, ['countryId' => $countryId])
            ->fetch();

        return $result ?: null;
    }

    public function getTableColumns(string $tableName): array
    {
        return $this
            ->query("SHOW FULL COLUMNS FROM `{$tableName}`")
            ->fetchAll();
    }

    public function getHtmlTemplate(int $eventTypeId): string
    {
        $sql = 'SELECT html FROM c_tipos WHERE id = :eventType';

        $eventType = $this
            ->query($sql, ['eventType' => $eventTypeId])
            ->fetch();

        return $eventType['html'];
    }
}

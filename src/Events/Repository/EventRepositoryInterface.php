<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Events\Repository;

interface EventRepositoryInterface
{
    /**
     * Encontra um evento pelo nome da sua tabela de leads.
     *
     * @param  string  $tableName  O nome da tabela (ex: '11_brasil_lancamento_tracker_2026').
     * @return array|null Retorna os dados do evento ou nulo se não encontrar.
     */
    public function findByTableName(string $tableName): ?array;

    /**
     * Encontra um evento pelo seu ID no briefing.
     *
     * @param  int  $briefingId  O ID do evento.
     * @return array|null Retorna os dados do evento ou nulo se não encontrar.
     */
    public function findById(int $briefingId): ?array;

    /**
     * Busca as informações do país relacionado a um evento.
     *
     * @param  int  $countryId  O ID do país.
     * @return array|null Retorna os dados do país ou nulo se não encontrar.
     */
    public function findCountryInfo(int $countryId): ?array;

    /**
     * Busca as colunas de uma determinada tabela de evento.
     *
     * @param  string  $tableName  O nome da tabela.
     * @return array Retorna a lista de colunas.
     */
    public function getTableColumns(string $tableName): array;
}

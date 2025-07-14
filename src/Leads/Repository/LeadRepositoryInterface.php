<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Repository;

interface LeadRepositoryInterface
{
    /**
     * Busca o último ID de lead enviado para o CRM para um determinado evento.
     *
     * @param int $eventId O ID do evento.
     * @return int O último ID enviado.
     */
    public function findLastSentLeadId(int $eventId): int;

    /**
     * Busca todos os leads de uma tabela que ainda não foram enviados.
     *
     * @param string $tableName A tabela de leads do evento.
     * @param int $lastSentId O ID do último lead enviado.
     * @return array A lista de novos leads.
     */
    public function findUnsentLeads(string $tableName, int $lastSentId): array;

    /**
     * Salva o registro de uma tentativa de envio ao CRM.
     *
     * @param int $eventId O ID do evento.
     * @param int $lastSentId O último ID de lead no lote.
     * @param string $responseMessage A resposta do CRM (sucesso ou erro).
     * @return void
     */
    public function logCrmSubmission(int $eventId, int $lastSentId, string $responseMessage): void;

    /**
     * Insere um novo lead na tabela de evento especificada.
     *
     * @param string $tableName A tabela onde o lead será inserido.
     * @param array $leadData Os dados do lead.
     * @return void
     */
    public function save(string $tableName, array $leadData): void;

    /**
     * Inicia uma transação no banco de dados.
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Confirma a transação atual.
     * @return bool
     */
    public function commit(): bool;

    /**
     * Reverte a transação atual.
     * @return bool
     */
    public function rollBack(): bool;

    /**
     * Verifica se uma transação está ativa.
     * @return bool
     */
    public function inTransaction(): bool;
}
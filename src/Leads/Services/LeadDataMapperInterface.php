<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services;

interface LeadDataMapperInterface
{
    /**
     * Mapeia os dados de um lead para um array de inserção.
     *
     * @param  array  $leadFields  Os campos do lead vindos do DTO.
     * @param  array  $tableColumns  As colunas válidas da tabela de destino.
     * @return array O array de dados pronto para ser salvo.
     */
    public function mapToInsertable(array $leadFields, array $tableColumns): array;
}

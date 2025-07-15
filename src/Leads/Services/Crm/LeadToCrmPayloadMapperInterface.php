<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services\Crm;

interface LeadToCrmPayloadMapperInterface
{
    /**
     * Mapeia um array de leads para o formato de payload esperado pelo CRM.
     *
     * @param  array  $leads  A lista de leads a serem mapeados.
     * @param  array  $eventInfo  Os dados do evento do briefing.
     * @param  array  $countryInfo  Os dados do país do evento.
     * @return array O payload completo pronto para ser enviado.
     */
    public function map(array $leads, array $eventInfo, array $countryInfo): array;
}

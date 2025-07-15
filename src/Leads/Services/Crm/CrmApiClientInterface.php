<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services\Crm;

use GuzzleHttp\Exception\GuzzleException;

interface CrmApiClientInterface
{
    /**
     * Envia um lote de leads para a API do CRM.
     *
     * @param  array  $payload  O array de leads formatados.
     * @param  string  $token  O token de autenticação.
     * @return string O corpo da resposta da API em caso de sucesso.
     *
     * @throws GuzzleException em caso de falha na comunicação.
     */
    public function sendLeads(array $payload, string $token): string;
}

<?php

declare(strict_types=1);

namespace Ovlk\GMLeads\Leads\Services\Crm;

interface CrmAuthenticatorInterface
{
    /**
     * Obtém um token de autenticação válido do CRM.
     *
     * @return string O token de autenticação.
     *
     * @throws \Exception em caso de falha na autenticação.
     */
    public function getAuthToken(): string;
}
